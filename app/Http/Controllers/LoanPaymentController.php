<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\PaymentSchedule;
use App\Utils\TransactionUtil;
use App\Utils\ModuleUtil;
use App\Utils\ProductUtil;
use App\Utils\BusinessUtil;
use App\Utils\ContactUtil;
use App\Utils\NotificationUtil;
use App\Loan;
use App\Transaction;
use App\TransactionPayment;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use App\Contact;
use App\Delay;
use App\Mail\NotificacionPrestamoLibra;
use Illuminate\Support\Facades\Mail;
use App\AccountTransaction;
use App\Business;
use App\Events\TransactionPaymentAdded;
use App\ScheduleVersion;
use App\PaymentApplication;
use App\ExchangeRates;

class LoanPaymentController extends Controller
{
    /**
     * All Utils instance.
     */
    protected $transactionUtil;
    protected $moduleUtil;
    protected $productUtil;
    protected $businessUtil;
    protected $contactUtil;
    protected $notificationUtil;
    /**
     * Create a new controller instance.
     *
     * @param  ProductUtils  $product
     * @return void
     */
   public function __construct(NotificationUtil $notificationUtil,TransactionUtil $transactionUtil,ModuleUtil $moduleUtil, BusinessUtil $businessUtil, ProductUtil $productUtil, ContactUtil $contactUtil)
    {
        $this->productUtil = $productUtil;
        $this->moduleUtil = $moduleUtil;
        $this->businessUtil = $businessUtil;
        $this->contactUtil = $contactUtil;
        $this->transactionUtil = $transactionUtil;
        $this->notificationUtil = $notificationUtil;
    }

    //Pago de las cuotas por medio del calendario de pagos
    public function store(Request $request){
        
        try {
            $type_pay = $request->input('optionPay') ? $request->input('optionPay') : 1;//Tipo de pago 1 regular y 2 pago adelantado
            $payment_shedule = PaymentSchedule::find($request->payment_schedule_id);
            $mount_quota = $payment_shedule->mount_quota;//Cantidad de la letra que se tiene que pagar
            $gps_quota = $payment_shedule->gps_quota; 
            $sure_quota = $payment_shedule->sure_quota;
            $admin_fee_quota = $payment_shedule->admin_fee_quota;
            $initial = $payment_shedule->initial;
            $mount_quota_total = $mount_quota + $gps_quota + $sure_quota + $initial + $admin_fee_quota;
            //Calcular el monto que falta pagar
            $amount_paid = 0; //Monto ya pagado
            $transactionPayments = TransactionPayment::where('payment_schedule_id', $request->payment_schedule_id)->get();
            if($transactionPayments){
                foreach ($transactionPayments as $transactionPayment) {
                    $amount_paid =  $amount_paid + $transactionPayment->amount;
                }
            }
            $missing_amount = round($mount_quota_total - $amount_paid,2);// Cantidad que falta pagar
            $amount = $this->transactionUtil->num_uf($request->amount); //cantidad que se está pagando
            //nota
            $note = "";
            $note .= $request->input('note').'. ';
            if($type_pay == 2){
                $days_in_advance = $request->input('days_in_advance');
                $interestSaved =   ($amount * 0.00111) * $days_in_advance;
                $note .= 'Por pago adelantado de '.$days_in_advance.' días está ahorrando '. number_format($interestSaved,2) .'Dolares. ';
            }

            //El monto a pagar no puede ser superior a la cuota 
            if( round($amount,2) <= round($missing_amount,2)){
                // metodo de pago
                $account_id = null;
                if(! empty($request->input('account_id'))){
                    $account_id = $request->input('account_id');
                }
                //Si es Soles
                if($request->currency !='Dolar'){
                    //pago adelantado
                    $note .= 'Se pago '.$request->amount_var.' '. $request->currency.' con tipo de cambio '. $request->exchange_rate.'. ';
                }
            
                //fecha de pago
                if($request->paid_on){
                    $paid_on = $this->transactionUtil->uf_date($request->input('paid_on'), true);
                }else{
                    $rightNow = Carbon::now();
                    $paid_on = $rightNow->toDateTimeString();
                }
                //Crear el pagon de la cuota
                $loan = Loan::find($payment_shedule->loan_id);
                $transaction = Transaction::find($loan->transaction_id);
                $transactionPaymentNew =  $this->transactionUtil->newTransaction(
                    $transaction, 
                    $amount, 
                    $loan->user_id, 
                    $loan->customer_id, 
                    $note, 
                    $paid_on, 
                    $request->method,
                    $payment_shedule->id, 
                    $account_id
                );
                //----------Add Accouny Transaction---------
                if(! empty($request->input('account_id'))){
                    $account_transaction_data = [
                        'account_id' => $account_id,
                        'type' =>'credit',
                        'amount' => $amount,
                        'operation_date' =>  $paid_on,
                        'created_by' => $loan->user_id,
                        'transaction_id' => $transaction->id,
                        'transaction_payment_id' =>  $transactionPaymentNew->id,
                    ];
                    AccountTransaction::createAccountTransaction($account_transaction_data);
                }

                //REGISTRAR SI ES UN PAGO ADELANTADO
                if($type_pay == 2){
                    //$days_period = $this->daysInPeriod($payment_shedule, $loan->date);
                    //$interestSaved =  $this->transactionUtil->getDiscountPayAvance($days_period, $days_in_advance,$payment_shedule->interests);
                    //REGISTRAR EL DESCUENTO EN LA TRANSACCION
                    $transaction->discount_type = 'fixed';
                    $transaction->discount_amount =  $transaction->discount_amount + $interestSaved;
                    $transaction->final_total = $transaction->final_total - $interestSaved;
                    $transaction->save();
                    //REGISTRAR LA APLICACION DEL PAGO A CAPITAL
                    $paymentApplication = PaymentApplication::create([
                        'loan_id' =>  $payment_shedule->loan_id,
                        'transaction_id'=> $transaction->id,
                        'transaction_payment_id' => $transactionPaymentNew->id,
                        'payment_schedule_id' => $payment_shedule->id,
                        'concept' => 'Pago adelantado de una letra',
                        'amount' => $amount,
                        'amount_discounted' => $interestSaved,// El descuento es por pago adelantado
                        'days_in_advance' =>  $days_in_advance,
                        'applied_at' => Carbon::now(),
                    ]);
                }
                //--------Cambiar el estado de la cuota a pagado o paga_parcial
                if(round($amount,2) == round($missing_amount,2)){
                    $payment_shedule->status = "paid";
                    $payment_shedule->save();
                }else{ 
                    $payment_shedule->status = "partial";
                    $payment_shedule->save();
                }
                DB::commit();
                $output = ['success' => true,
                    'msg' => __('purchase.payment_added_success'),
                ];
            }else{
                $msg = __('El pago no puede ser mayor a la letra del prestamo');
                $output = ['success' => false,
                    'msg' => $msg,
                ];
            }
        } catch (\Exception $th) {
            DB::rollBack();
            \Log::emergency('ERROR PAGO:'.$th->getMessage().''.' IN FILE:'.$th->getFile().' LINE:'.$th->getLine());
            $msg = __('messages.something_went_wrong');
            $output = ['success' => false,
                'msg' => $msg,
            ];
        }
        return redirect()->back()->with(['status' => $output]);
    }

    //Pago a capital total - currency
     public function payCapital(Request $request){
        try {
            $type_pay = $request->input('type_pay'); //partial o total
            $business_id = $request->session()->get('user.business_id');
            $transaction_id = $request->input('transaction_id');
            $transaction = Transaction::where('business_id', $business_id)->findOrFail($transaction_id);
            $transaction_before = $transaction->replicate();
            if (! (auth()->user()->can('purchase.payments') || auth()->user()->can('sell.payments') || auth()->user()->can('all_expense.access') || auth()->user()->can('view_own_expense'))) {
                abort(403, 'Unauthorized action.');
            }
            if ($transaction->payment_status != 'paid') {
                $inputs = $request->only(['amount', 'method', 'note', 'card_number', 'card_holder_name',
                    'card_transaction_number', 'card_type', 'card_month', 'card_year', 'card_security',
                    'cheque_number', 'bank_account_number', ]);
                $note = 'Pago a capital. ';
                $note .= $request->input('currency') !='Dolar' ? $request->input('amount_var').' '. $request->input('currency').' con tipo de cambio '. $request->input('exchange_rate').'. ' : '';
                $note .= $request->input('note');

                $inputs['note'] = $note;
                $inputs['paid_on'] = $this->transactionUtil->uf_date($request->input('paid_on'), true);
                $inputs['transaction_id'] = $transaction->id;
                $inputs['amount'] = $this->transactionUtil->num_uf($inputs['amount']);
                $inputs['created_by'] = auth()->user()->id;
                $inputs['payment_for'] = $transaction->contact_id;

                if ($inputs['method'] == 'custom_pay_1') {
                    $inputs['transaction_no'] = $request->input('transaction_no_1');
                } elseif ($inputs['method'] == 'custom_pay_2') {
                    $inputs['transaction_no'] = $request->input('transaction_no_2');
                } elseif ($inputs['method'] == 'custom_pay_3') {
                    $inputs['transaction_no'] = $request->input('transaction_no_3');
                }

                if (! empty($request->input('account_id')) && $inputs['method'] != 'advance') {
                    $inputs['account_id'] = $request->input('account_id');
                }

                $prefix_type = 'purchase_payment';
                if (in_array($transaction->type, ['sell', 'sell_return'])) {
                    $prefix_type = 'sell_payment';
                } elseif (in_array($transaction->type, ['expense', 'expense_refund'])) {
                    $prefix_type = 'expense_payment';
                }

                DB::beginTransaction();

                $ref_count = $this->transactionUtil->setAndGetReferenceCount($prefix_type);
                //Generate reference number
                $inputs['payment_ref_no'] = $this->transactionUtil->generateReferenceNumber($prefix_type, $ref_count);
                $inputs['business_id'] = $request->session()->get('business.id');
                $inputs['document'] = $this->transactionUtil->uploadFile($request, 'document', 'documents');
                $contact_balance = ! empty($transaction->contact) ? $transaction->contact->balance : 0;
                if ($inputs['method'] == 'advance' && $inputs['amount'] > $contact_balance) {
                    throw new AdvanceBalanceNotAvailable(__('lang_v1.required_advance_balance_not_available'));
                }

                if (! empty($inputs['amount'])) {
                    $tp = TransactionPayment::create($inputs);
                    if (! empty($request->input('denominations'))) {
                        $this->transactionUtil->addCashDenominations($tp, $request->input('denominations'));
                    }
                    $inputs['transaction_type'] = $transaction->type;
                    event(new TransactionPaymentAdded($tp, $inputs));
                }
                //update payment status
                $payment_status = $this->transactionUtil->updatePaymentStatus($transaction_id, $transaction->final_total);
                $transaction->payment_status = $payment_status;
                $this->transactionUtil->activityLog($transaction, 'payment_edited', $transaction_before);
                //obetener la version actual del calendario de pagos
                $interestSaved = 0;
                $schedule_version_current_id = 0;
                $schedule_version_current = ScheduleVersion::where('loan_id', $request->input('loan_id'))->where('status','active')->first();
                    //DESACTIVAR LA VERSION ACTUAL DEL CALENDARIO
                    if (isset($schedule_version_current)) {
                        $schedule_version_current->update(['status' => 'disabled']);
                        $schedule_version_current_id = $schedule_version_current->id;
                    }
                    //CREAR UNA NUEVA VERSION DEL CALENDARIO
                    $schedule_version_new = ScheduleVersion::create([
                        'loan_id' => $request->input('loan_id'),
                        'transaction_payment_id' => $tp->id,
                        'status' => 'active',
                        'reason' => 'Pago de capital',
                        'generated_at' => $tp->paid_on,
                    ]);

                    $interestSaved =  $this->transactionUtil->regeneratePaymentSchedule($request->input('loan_id'),$schedule_version_current_id, $schedule_version_new->id, $inputs['amount'], $type_pay);
                    $concept = ($type_pay == 'total') ? 'Pago capital total' : 'Pago capital parcial';
                    
                //REGISTRAR EL DESCUENTO EN LA TRANSACCION
                $transaction->discount_type = 'fixed';
                $transaction->discount_amount =  $transaction->discount_amount +$interestSaved;
                $transaction->final_total = $transaction->final_total - $interestSaved;
                $transaction->save();
                //REGISTRAR LA APLICACION DEL PAGO A CAPITAL
                $paymentApplication = PaymentApplication::create([
                    'loan_id' => $request->input('loan_id'),
                    'transaction_id'=> $transaction_id,
                    'transaction_payment_id' => $tp->id,
                    'concept' => $concept,
                    'amount' => $inputs['amount'],
                    'amount_discounted' => $interestSaved, // El descuento es por pago adelantado o a capital
                    'applied_at' => Carbon::now(),
                ]);
                DB::commit();
            }

            $output = ['success' => true,
                'msg' => __('purchase.payment_added_success'),
            ];

        } catch (\Throwable $th) {
            DB::rollBack();
            \Log::emergency('ERROR IN PAY CAPITAL BECAUSE:'.$th->getMessage().''.' IN FILE:'.$th->getFile().' LINE:'.$th->getLine());
            $msg = __('messages.something_went_wrong');
            $output = ['success' => false,
                'msg' => $msg,
            ];
        }
        return redirect()->back()->with(['status' => $output]);
    }

    public function statemenPDF($id){

        $loan = Loan::find($id);
        // ¿hay versión activa para este préstamo?
        $hasActiveVersion = DB::table('payment_schedules as psx')
            ->join('schedule_versions as svx', 'svx.id', '=', 'psx.schedule_version_id')
            ->where('psx.loan_id', $loan->id)
            ->where('svx.status', 'active')
            ->exists();

        $psBase = PaymentSchedule::query()
            ->from('payment_schedules as ps')
            ->leftJoin('schedule_versions as sv', 'sv.id', '=', 'ps.schedule_version_id')
            ->where('ps.loan_id', $loan->id)
            ->when(
                $hasActiveVersion,
                fn ($q) => $q->where('sv.status', 'active'),
                fn ($q) => $q->whereNull('ps.schedule_version_id')
            )
            ->select('ps.*');

        $tbl_ps = (clone $psBase)->orderBy('ps.sheduled_date')->get();
        $annexes = json_decode($loan->annexes);

        //#AGREGARLE CON MAP Los pagos de las transacciones 
        $paymentShedules = $tbl_ps->map(function($query){
            $references = [];
            $payment_schedule_id = $query->ref_payment_schedule_id ? $query->ref_payment_schedule_id : $query->id;
            $transactionPayments = TransactionPayment::where('payment_schedule_id', $payment_schedule_id)->get();
            foreach($transactionPayments as $transactionPayment){
                array_push($references,$transactionPayment->payment_ref_no);
            }
            $query->references = $references;
            return $query;
        });


        //CORRIGE EL PROBLEMA DE N+1
        // $psIds = $tbl_ps->pluck('id');
        // $refsByPs = TransactionPayment::whereIn('payment_schedule_id', $psIds)
        //     ->pluck('payment_ref_no', 'payment_schedule_id')
        //     ->groupBy(fn($ref, $psId) => $psId);

        // $paymentShedules = $tbl_ps->map(function($ps) use ($refsByPs) {
        //     $ps->references = ($refsByPs[$ps->id] ?? collect())->values()->all();
        //     return $ps;
        // });

        $customer = Contact::find($loan->customer_id);
        $dateNow = Carbon::now();
        $startFechaLoan = Carbon::parse($loan->date);
        //Calcular el día que termina el prestamo
        $fechaLoan = Carbon::parse($loan->date);
        $endOfLoan = $fechaLoan->addMonths($loan->number_month);
        $business_id = request()->session()->get('user.business_id');

        $query = Transaction::where('business_id', $business_id)
                    ->where('id', $loan->transaction_id)
                    ->with(['contact', 'sell_lines' => function ($q) {
                        $q->whereNull('parent_sell_line_id');
                    }, 'sell_lines.product', 'sell_lines.product.unit', 'sell_lines.product.second_unit', 'sell_lines.variations', 'sell_lines.variations.product_variation', 'payment_lines', 'sell_lines.modifiers', 'sell_lines.lot_details', 'tax', 'sell_lines.sub_unit', 'table', 'service_staff', 'sell_lines.service_staff', 'types_of_service', 'sell_lines.warranties', 'media']);
        if (! auth()->user()->can('sell.view') && ! auth()->user()->can('direct_sell.access') && auth()->user()->can('view_own_sell_only')) {
            $query->where('transactions.created_by', request()->session()->get('user.id'));
        }
        $sell = $query->firstOrFail();

        //Calcular total_paid
        $total_paid = 0;
        foreach($sell->payment_lines as $key=>$payment_line){
            if($payment_line->is_return == 1){
                $total_paid -= $payment_line->amount;
            } else {
                $total_paid += $payment_line->amount;
            }
        }
        //Calular cuanto tiene o le falta pagar en el mes, en caso ya pago 0 Dolares
        $inicioMes = Carbon::now()->startOfMonth(); // Primer día del mes
        $finMes = Carbon::now()->endOfMonth();      // Último día del mes
      
        $payment_schedule = (clone $psBase)
            ->whereBetween('ps.sheduled_date', [$inicioMes, $dateNow])
            ->orderBy('ps.sheduled_date', 'asc')
            ->first();

        $amount_to_pay = 0;
        $default_interest = 0;
        $interest_paid = 0;

            if($payment_schedule){ 
                $amount_to_pay = $this->transactionUtil->amountToPay($payment_schedule); //Cantidad a pagar
            }

            $default_interest = 0;
            $amount_condonate = 0;
            $delays =  Delay::where('loan_id',$loan->id)->get(); //TODAS LAS MORAS 
            foreach($delays as $delay){

                if($delay->status == 'late'){
                    $default_interest += $delay->late_amount;
                }else{
                     if( $delay->status != 'condone')
                     $interest_paid += $delay->late_amount;
                }
                
                if($delay->status == 'partial'){
                    $transaction_delay =  TransactionPayment::where('delay_id',$delay->id)->first();
                    if($transaction_delay)
                    $amount_condonate +=  ($delay->late_amount - $transaction_delay->amount);
                }
                
            }
        
        // Calcula la deuda de las letras hasta el momento 
        $paymentSchedules = (clone $psBase)
            ->whereBetween('ps.sheduled_date', [$startFechaLoan, $dateNow])
            ->orderBy('ps.sheduled_date', 'asc')
            ->get();
                
        $amount_months_late = 0;
        $total_month_now = 0;
        foreach ($paymentSchedules as $paymentSchedule) {
             $total_month_now += $paymentSchedule->getQuote();        
        }

        $initial =  bcsub($loan->initial_amount, $loan->initial_fraction,4); //Calcular la inicial pagada
        $total_bills_payable = $total_paid - ($initial + $interest_paid + $loan->admin_fee + $loan->gps + $loan->insurance ); //Todos los pagos menos la Inicial y menos los intereses pagados
        $amount_months_late =  bcsub($total_month_now, $total_bills_payable, 4);  //Es el pago total de lo que debe incluido el mes actual (No esta incluido los intereses moratorio)

        if( $amount_months_late < 0)
            $amount_months_late = 0;

        $months_behind = bcsub($amount_months_late, $amount_to_pay, 4);
        //RESTAR la cantidad condonada 
        $months_behind = bcsub($months_behind, $amount_condonate, 4);

        //HAY ACARREO DE LOS DECIMALES DE LOS PAGOS QUE SE COBRANE EN 2 DIGITOS PERO LOS REALES SON DE 4 DIGITOS
        if($months_behind < 0.15){
            $months_behind = 0;
        }
    
        $moras = Delay::where('loan_id',$loan->id)->where('status','late')->get();
        //Generar PDF
        $pdf = Pdf::loadView('loan.pdf',compact('moras','annexes','months_behind','amount_months_late','amount_to_pay','default_interest','paymentShedules','loan','customer','sell','dateNow','total_paid','endOfLoan'));
        return $pdf->download($loan->type_product.'.pdf');
    }


    

    //  private function daysInPeriod(PaymentSchedule $current, string $loanStartDate){

    //     $prev = PaymentSchedule::where('loan_id', $current->loan_id)
    //     ->where('schedule_version_id', $current->schedule_version_id) // si usas versiones
    //     ->where('number_quota', '<', $current->number_quota)
    //     ->orderBy('number_quota', 'desc')
    //     ->first();

    //     $prevScheduledDate = $prev?->scheduled_date;
    //     $scheduledDate = $current->sheduled_date;

    //     $end = Carbon::parse($scheduledDate)->startOfDay();
    //     if ($prevScheduledDate) {
    //         $start = Carbon::parse($prevScheduledDate)->startOfDay();
    //     } else {
    //         $start = Carbon::parse($loanStartDate)->startOfDay();
    //     }

    //     // Días entre inicio (exclusivo) y fin (inclusive para devengo típico)
    //     // En la práctica bancaria suele bastar diffInDays().
    //     return max(1, $start->diffInDays($end));
    // }


}







