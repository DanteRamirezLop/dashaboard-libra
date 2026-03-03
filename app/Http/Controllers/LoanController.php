<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Utils\ModuleUtil;
use Yajra\DataTables\Facades\DataTables;
use App\Contact;
use App\Loan;
use App\Utils\ProductUtil;
use App\Utils\BusinessUtil;
use Carbon\Carbon;
use App\Product;
use App\User;
use App\Category;
use App\Utils\ContactUtil;
use App\Variation;
use App\LoanSetting;
use App\Delay;
use App\PaymentSchedule;
use App\Utils\TransactionUtil;
use Illuminate\Support\Facades\DB;
use App\BusinessLocation;
use App\Transaction;
use App\TransactionSellLine;
use App\TransactionSellLinesPurchaseLines;
use App\TransactionPayment;
use App\InvoiceScheme;
use App\ScheduleVersion;
use App\PaymentApplication;
use App\ExchangeRates;



class LoanController extends Controller {
    /**
     * All Utils instance.
     */
    protected $transactionUtil;
    protected $moduleUtil;
    protected $productUtil;
    protected $businessUtil;
    protected $contactUtil;
    /**
     * Create a new controller instance.
     *
     * @param  ProductUtils  $product
     * @return void
     */
    public function __construct(TransactionUtil $transactionUtil,ModuleUtil $moduleUtil, BusinessUtil $businessUtil, ProductUtil $productUtil, ContactUtil $contactUtil)
    {
        $this->productUtil = $productUtil;
        $this->moduleUtil = $moduleUtil;
        $this->businessUtil = $businessUtil;
        $this->contactUtil = $contactUtil;
        $this->transactionUtil = $transactionUtil;
    }

    public function index(){   
        if (request()->ajax()) {
            $business_id = request()->session()->get('user.business_id');
           $psAgg = DB::table('payment_schedules as ps')
            ->leftJoin('schedule_versions as sv', 'sv.id', '=', 'ps.schedule_version_id')
            ->selectRaw("
                ps.loan_id,
                COALESCE(SUM(
                    CASE WHEN ps.status <> 'pending'
                    THEN ps.mount_quota + ps.gps_quota + ps.sure_quota + ps.admin_fee_quota + ps.initial
                    ELSE 0 END
                ),0) as delay,

                COALESCE(SUM(
                    CASE WHEN ps.status = 'pending'
                    THEN ps.mount_quota + ps.gps_quota + ps.sure_quota + ps.admin_fee_quota + ps.initial
                    ELSE 0 END
                ),0) as for_due
            ")
            ->where(function ($q) {
                // Subquery correlacionado: ¿este loan_id tiene alguna versión activa?
                $activeVersionExists = function ($sq) {
                    $sq->selectRaw('1')
                    ->from('payment_schedules as psx')
                    ->join('schedule_versions as svx', 'svx.id', '=', 'psx.schedule_version_id')
                    ->whereColumn('psx.loan_id', 'ps.loan_id')
                    ->where('svx.status', 'active')
                    ->limit(1);
                };
                $q
                // CASO A: Si NO existe versión activa => usa SOLO originales (NULL)
                ->where(function ($q1) use ($activeVersionExists) {
                    $q1->whereNotExists($activeVersionExists)
                    ->whereNull('ps.schedule_version_id');
                })
                // CASO B: Si SÍ existe versión activa => usa SOLO filas de esa(s) versión(es) activa(s)
                ->orWhere(function ($q2) use ($activeVersionExists) {
                    $q2->whereExists($activeVersionExists)
                    ->where('sv.status', 'active');
                });
            })
            ->groupBy('ps.loan_id');

             $dAgg = DB::table('delays as d')
            ->selectRaw("
                d.loan_id,
                COALESCE(SUM(
                    CASE WHEN d.status = 'late'
                    THEN d.late_amount
                    ELSE 0 END
                ),0) as mora
            ")
            ->whereNull('d.deleted_at')
            ->groupBy('d.loan_id');

            $loans = Loan::query()
            ->leftJoin('transactions', 'loans.transaction_id', '=', 'transactions.id')
            ->leftJoinSub($psAgg, 'psa', function ($join) {
                $join->on('psa.loan_id', '=', 'loans.id');
            })
            ->leftJoinSub($dAgg, 'da', function ($join) {
                $join->on('da.loan_id', '=', 'loans.id');
            })
            ->where('loans.business_id', $business_id)
            ->where('loans.status', '!=', 'quotation')
            ->select(
                'loans.id',
                'loans.balance_to_financed',
                'loans.total_cost_loan',
                'loans.created_at',
                'loans.transaction_id',
                'loans.status',
                'loans.vin',
                'loans.customer_name',
                'loans.product_name',
                'loans.number_month',
                'loans.waiter',
                'transactions.final_total as final_total',
                DB::raw('(SELECT SUM(IF(TP.is_return = 1,-1*TP.amount,TP.amount))
                        FROM transaction_payments AS TP
                        WHERE TP.transaction_id = transactions.id) as total_paid'),
       
                DB::raw('(SELECT 
                            SUM(IF(TP.is_return = 1,-1*TP.amount,TP.amount))
                            FROM transaction_payments AS TP
                            WHERE TP.transaction_id = transactions.id AND TP.payment_schedule_id IS NOT NULL
                        ) as total_only_payments'),

                DB::raw('COALESCE(psa.delay,0) as delay'),
                DB::raw('COALESCE(da.mora,0) as mora'),
                DB::raw('COALESCE(psa.for_due,0) as for_due'),
            )->get();
        
            return Datatables::of($loans)->addColumn(
                    'action',
                     function ($row){
                             if (auth()->user()->can('user.view') || auth()->user()->can('user.create') || auth()->user()->can('roles.view')){                
                                $html = '<div class="btn-group">
                                    <button type="button" class="tw-dw-btn tw-dw-btn-xs tw-dw-btn-outline  tw-dw-btn-info tw-w-max dropdown-toggle"
                                        data-toggle="dropdown" aria-expanded="false">'.
                                        __('messages.actions').
                                        '<span class="caret"></span><span class="sr-only">Toggle Dropdown
                                        </span>
                                    </button>
                                        <ul class="dropdown-menu dropdown-menu-left" role="menu">.   
                                            <li><a href="'.route('add-letter-loan',[$row->id]).'"><i class="fa fa-list" aria-hidden="true"></i> Asignar número de letra</a></li>';

                                $html .= '<li class="divider"></li>';
                                $html .= '<li><a href="'.action([\App\Http\Controllers\LoanController::class, 'show'], [$row->id]).'" "><i class="fas fa fa-calendar" aria-hidden="true"></i> Calendario de pagos</a></li>';
                                $html .= '<li class="divider"></li>';
                                $html .= '<li><a href="'.action([\App\Http\Controllers\LoanPaymentController::class, 'statemenPDF'], [$row->id]).'"  ><i class="fas fa fa-download" aria-hidden="true"></i> Descargar estado de cuenta</a></li>';
                                $html .= '<li><a href="#" class="print-invoice" data-href="'.route('sell.printInvoice', [$row->transaction_id]).'"><i class="fas fa-print" aria-hidden="true"></i> '.__('lang_v1.print_invoice').'</a></li>';
                                $html .= '<li><a href="'.action([\App\Http\Controllers\TransactionPaymentController::class, 'show'], [$row->transaction_id]).'" class="view_payment_modal"><i class="fas fa-money-bill-alt"></i> '.__('purchase.view_payments').'</a></li>';
                                $html .= '<li><a href="#" data-href="'.action([\App\Http\Controllers\SellController::class, 'show'], [$row->transaction_id]).'" class="btn-modal" data-container=".view_modal"><i class="fas fa-eye" aria-hidden="true"></i> '.__('messages.view').'</a></li>';
                                $html .= '<li><a href="'.action([\App\Http\Controllers\LoanController::class, 'destroy'], [$row->id]).'" class="delete-sale"> <i class="fas fa-trash"></i> '.__('messages.delete').'</a></li>';
                        }else{
                          $html = '<div class="btn-group">
                                    <button type="button" class="btn btn-info dropdown-toggle btn-xs" 
                                        data-toggle="dropdown" aria-expanded="false">'.
                                        __('messages.actions').
                                        '<span class="caret"></span><span class="sr-only">Toggle Dropdown
                                        </span>
                                    </button>
                                     <ul class="dropdown-menu dropdown-menu-left" role="menu">
                                     <li><a href="'.action([\App\Http\Controllers\LoanPaymentController::class, 'statemenPDF'], [$row->id]).'"  ><i class="fas fa fa-download" aria-hidden="true"></i> Descargar estado de cuenta</a></li>';

                            $html .= '<li class="divider"></li>';
                            $html .= '<li><a href="'.action([\App\Http\Controllers\LoanController::class, 'show'], [$row->id]).'" "><i class="fas fa fa-calendar" aria-hidden="true"></i> Calendario de pagos</a></li>';
                        } 
                        
                        $html .= '</ul></div>';
                        return $html;
                     }
                )->addColumn(
                    'label',
                    function ($row){
                        switch ($row->status) {
                            case "approved":
                                $label = '<span class="label label-info">Aprobado</span>';
                                break;
                            case"partial":
                                $label = '<span class="label label-info">Parcial</span>';
                                break;
                            case"in arrears":
                                $label = '<span class="label label-danger">Atrasado</span>';
                                break;
                            case"cancelled":
                                $label = '<span class="label label-default">Cancelado</span>';
                                break;
                            case"paid":
                                $label = '<span class="label label-success">pagado</span>';
                                break;
                        }
                        return $label;
                     }
                )
                 ->addColumn('total_delay', function ($row) {
                     
                   $mora = round($row->mora);
                    if($mora){
                        $total_delay = bcsub($row->delay, $row->total_only_payments, 4);
                    }else{
                        $total_delay = 0;
                    }

                     if($total_delay < 0 && $total_delay > -0.25) {
                        $total_delay = 0;
                    }
                    $total_delay_html = '<span class="payment_due" data-orig-value="'.$total_delay.'">'.$this->transactionUtil->num_f($total_delay, true).'</span>';
                    return $total_delay_html;
                })

                ->addColumn('total_to_delay', function ($row) {
                    $mora = round($row->mora);
                    if($mora){
                        $paid_partial = 0;
                    }else{
                        $paid_partial = bcsub($row->delay, $row->total_only_payments, 4);
                    }
                    $total_to_delay =  $row->for_due + $paid_partial;
                    $total_to_delay_html = '<span class="payment_due" data-orig-value="'.$total_to_delay.'">'.$this->transactionUtil->num_f($total_to_delay, true).'</span>';
                    return $total_to_delay_html;
                })
                ->addColumn('total_remaining', function ($row) {
                     $mora = round($row->mora);
                    if($mora){
                         $total_remaining = bcsub($row->delay, $row->total_only_payments, 4) + $row->mora;
                    }else{
                        $total_remaining = 0;
                    }
                    
                    if($total_remaining < 0 && $total_remaining > -0.25) {
                        $total_remaining = 0;
                    }
                    
                    $total_remaining_html = '<span class="payment_due" data-orig-value="'.$total_remaining.'">'.$this->transactionUtil->num_f($total_remaining, true).'</span>';
                    return $total_remaining_html;
                })

                 ->addColumn('total_mora', function ($row) {
                    $total_mora = $row->mora;
                    $total_mora_html = '<span class="payment_due" data-orig-value="'.$total_mora.'">'.$this->transactionUtil->num_f($total_mora, true).'</span>';
                    return $total_mora_html;
                })

                 ->addColumn('total_remaining_mora', function ($row) {
                    $total_remaining = $row->final_total - $row->total_paid;
                    $total_remaining_html = '<span class="payment_due" data-orig-value="'.$total_remaining.'">'.$this->transactionUtil->num_f($total_remaining, true).'</span>';
                    return $total_remaining_html;
                })

                ->editColumn(
                    'final_total',
                    '<span class="final-total" data-orig-value="{{$final_total}}"> @format_currency($final_total)  </span>'
                )
                ->editColumn(
                    'total_paid',
                    '<span class="total-paid" data-orig-value="{{$total_paid}}">@format_currency($total_paid)</span>'
                )

                ->editColumn('balance_to_financed','@format_currency(number_format($balance_to_financed))')
                ->editColumn('total_cost_loan','@format_currency(number_format($total_cost_loan))')
                ->editColumn('created_at','{{date("Y/m/d",strtotime($created_at))}}')
                ->rawColumns(['action','label','seller','total','final_total','total_paid','total_remaining','total_delay','total_to_delay','total_mora','total_remaining_mora'])
                ->make(true);
        }
        $type = request()->get('id');
        return view('loan.index',compact('type'));
    }

    public function show($id)
    {
        $business_id = request()->session()->get('user.business_id');
        $type = request()->get('type');
        $loan = Loan::find($id);
        $annexes = json_decode($loan->annexes);
        $countVersion = ScheduleVersion::where('loan_id', $loan->id)->count();
        $scheduleVersionId = ScheduleVersion::where('loan_id', $loan->id)
        ->where('status', 'active')
        ->value('id'); // trae solo el id (o null)

        $paymentSchedules = PaymentSchedule::where('loan_id', $loan->id)
            ->when($scheduleVersionId, fn ($q) => $q->where('schedule_version_id', $scheduleVersionId))
            ->get();

        $customer = Contact::find($loan->customer_id);
		$user = User::find($loan->user_id);
        $total =  $loan->amount + $loan->admin_fee + $loan->gps  + $loan->insurance  +  $loan->initial_amount + $loan->start_rate;
        // ver si no tiene cuotas parciales para habilitar el pago a capital                       
        $canPayCapital = !PaymentSchedule::where('loan_id',$loan->id)->whereNotIn('status',['paid','pending'])->exists();
        //hay mora?
        $there_is_mora =  Delay::where('loan_id',$loan->id)->where('status','late')->exists(); //Mora actual 
        return view('loan.show')->with(compact('countVersion','annexes','canPayCapital','there_is_mora','paymentSchedules','type','customer','loan','user','total'));
    }


     public function addCapital($loan_id,$type)
    {
        if (! auth()->user()->can('purchase.payments') && ! auth()->user()->can('sell.payments') && ! auth()->user()->can('all_expense.access') && ! auth()->user()->can('view_own_expense')) {
            abort(403, 'Unauthorized action.');
        }

        try {
            if (request()->ajax()) {
                $loan = Loan::find($loan_id);
                $schedule_version = ScheduleVersion::where('loan_id', $loan->id)->where('status','active')->first();
                $schedule_version_id = $schedule_version ? $schedule_version->id : NULL;
                $business_id = request()->session()->get('user.business_id');
                $transaction = Transaction::where('business_id', $business_id)->with(['contact', 'location'])->findOrFail( $loan->transaction_id);
                if ($transaction->payment_status != 'paid') {
                    $show_advance = in_array($transaction->type, ['sell', 'purchase']) ? true : false;
                    $payment_types = $this->transactionUtil->payment_types(null, false,$business_id);
                    $accounts = $this->moduleUtil->accountsDropdown($business_id, true, false, true);
                    $paid_on = Carbon::now()->toDateTimeString();
                    $rows = PaymentSchedule::query()
                    ->where('loan_id', $loan->id)
                    ->where('schedule_version_id', $schedule_version_id)
                    ->orderBy('id')
                    ->get();

                    $nextPending = $rows->firstWhere('status', 'pending');
                    if (!$nextPending) {
                        return; // no hay cuotas pendientes
                    }

                    $amount = (float) $nextPending->opening_balance;

                    if($type == 'total'){
                        $other_expense = $this->transactionUtil->getOtherExpensesPaid($loan->id); // Monto de otros gastos pagados como el gps, seguro que en si son tiene intereses solo estan fraccionados 
                        $amount = round($amount + $other_expense,4); 
                        $amount_formated = $this->transactionUtil->num_f($amount);
                        $view = view('loan.payment_total')->with(compact('transaction','loan','amount','amount_formated','paid_on','payment_types','accounts','type'))->render();
                    }else{
                        $amount_formated = $this->transactionUtil->num_f($amount);
                        $view = view('loan.payment_capital')->with(compact('transaction','loan','amount','amount_formated','paid_on','payment_types','accounts','type'))->render();
                    }

                    $output = ['status' => 'due','view' => $view];
                } else {
                    $output = ['status' => 'paid','view' => '','msg' => __('purchase.amount_already_paid'),];
                }
                return json_encode($output);
            }
            //code...
        } catch (\Throwable $th) {
           Log::emergency('File:'.$th->getFile().'Line:'.$th->getLine().'Message:'.$th->getMessage());
           $output = ['success' => false,
            'msg' => 'Error: '.$th->getLine().'Message:'.$th->getMessage(),
            ];  
        }
    }

    public function addPayment($payment_schedules_id){
        if (request()->ajax()) {
            //busco el tipo de cambio del dia
            $search_date = Carbon::now()->format('Y-m-d');
            $exchange_rates = ExchangeRates::where('search_date',$search_date)->first();
            $exchange_rates = $exchange_rates ? $exchange_rates->sale : 1;

            $exchange_rates = number_format($exchange_rates,3);
            
            $payment_schedule = PaymentSchedule::findOrFail($payment_schedules_id);
            if ($payment_schedule->payment_status != 'paid') {
                $business_id = request()->session()->get('user.business_id');
                 //Accounts
                $accounts = $this->moduleUtil->accountsDropdown($business_id, true, false, true);
                $show_advance = in_array($payment_schedule->type, ['sell', 'purchase']) ? true : false;
                $payment_types = $this->transactionUtil->payment_types(null, $show_advance,$business_id);
                //Buscar el metodo de pago vinculado al PaymentShadule y restarlo al monto total 
                $amount = $this->transactionUtil->amountToPay($payment_schedule);
                $paid_on = Carbon::now()->toDateTimeString();
                $view = view('loan.payment_row')->with(compact('exchange_rates','payment_schedule','amount','paid_on','payment_types','accounts'))->render();
                $output = ['status' => 'due','view' => $view, ];
            } else {
                $output = ['status' => 'paid','view' => '','msg' => __('purchase.amount_already_paid'),  ];
            }
            return json_encode($output);
        }
    }


     public function updateLetterAnnexe(Request $request){
        try {
            
            if($request->type == 'letter'){
                $item = PaymentSchedule::find($request->id);
                $item->number_letter = $request->value;
                $item->save();
            }

            if($request->type == 'annexe'){
                $loan =  Loan::find($request->id);
                $annexes = json_decode($loan->annexes);

                if ($request->celda == 'anexo_1') {
                    $annexes->anexo_1 = $request->value;
                }

                if ($request->celda == 'anexo_2') {
                    $annexes->anexo_2 = $request->value;
                }

                if ($request->celda == 'anexo_3') {
                    $annexes->anexo_3 = $request->value;
                }

                 if ($request->celda == 'anexo_4') {
                    $annexes->anexo_4 = $request->value;
                }

                $loan->annexes = json_encode($annexes);
                $loan->save();
            }

            $output = ['success' => true,'msg' => 'Actualizado',];
        } catch (\Exception $e) {
            \Log::emergency('File:'.$e->getFile().'Line:'.$e->getLine().'Message:'.$e->getMessage());
            $output = ['success' => false,'msg' => 'Error: '.$e->getLine().'Message:'.$e->getMessage(),];
        }
        return $output;
    }


    public function addLetterLoan($id){
        $business_id = request()->session()->get('user.business_id');
        $type = request()->get('type');
        $loan = Loan::find($id);
        $annexes = json_decode($loan->annexes);
        $paymentSchedules = PaymentSchedule::where('loan_id', $loan->id)->get();
        $customer = Contact::find($loan->customer_id);
		$user = User::find($loan->user_id);
        $total =  $loan->amount + $loan->admin_fee + $loan->gps  + $loan->insurance  +  $loan->initial_amount;
        return view('loan.number_letter')->with(compact('annexes','paymentSchedules','type','customer','loan','user','total'));
    }


    public function getPrices(Request $request){
        $options = '';
        $options .= '<option selected disabled >Selecciona un precio</option>';
        $variations = Variation::where('product_id',$request->id)->get();
        foreach ($variations as $key => $variation) {
            $options .= "<option  value='".$variation->sell_price_inc_tax."'> ".  number_format($variation->sell_price_inc_tax, 2) ." </option>";
        }
        return response()->json(['status' => true, 'options' =>  $options]);
    }

    public function getCustomerSunat(Request $request){
        
        $business_id = $request->session()->get('user.business_id');
        $created_by  = $request->session()->get('user.id');
        $type = $request->input('type'); // 'dni' o 'ruc'
        // 1) Validar tipo y leer valor
        if (!in_array($type, ['dni', 'ruc'])) {
            return response()->json(['status' => false, 'msg' => 'Tipo inválido']);
        }

        $value = $request->input($type); // dni o ruc
        if (empty($value)) {
            return response()->json(['status' => false, 'msg' => strtoupper($type) . ' requerido']);
        }
        // 2) Buscar contacto local
        $query = Contact::where('contact_id', $value)->where('business_id', $business_id);
        $contact = $query->first();
        // 3) Si existe, asegurar acceso y responder
        if ($contact) {
            $this->ensureContactAccess($contact, $query, $created_by);
            return response()->json([
                'status'     => true,
                'name'       => $type === 'dni' ? $contact->name : $contact->supplier_business_name,
                'contact_id' => $contact->id,
                'mobile'     => $contact->mobile,
                'email'      => $contact->email,
            ]);
        }

        // 4) Config por tipo (endpoint, payload)
        $config = [
            'dni' => [
                'url' => 'https://apiperu.dev/api/dni',
                'payload' => ['dni' => $value],
            ],
            'ruc' => [
                'url' => 'https://apiperu.dev/api/ruc',
                'payload' => ['ruc' => $value],
            ],
        ];

        // 5) Llamada API
        [$ok, $dataOrError] = $this->callApiPeru($config[$type]['url'], $config[$type]['payload']);

        if (!$ok) {
            return response()->json(['status' => false, 'msg' => 'ERROR']);
        }

        $data = $dataOrError;
        if (empty($data['success'])) {
            return response()->json(['status' => false, 'msg' => strtoupper($type) . ' no encontrado']);
        }

        // 6) Crear contacto según tipo
        $contact = $this->createContactFromApi($type, $data['data'], $business_id, $created_by);
        $contact->save();

        return response()->json([
            'status'     => true,
            'name'       => $type === 'dni' ? $contact->name : $contact->supplier_business_name,
            'contact_id' => $contact->id,
            'mobile'     => $contact->mobile,
            'email'      => $contact->email,
        ]);
    }

/**
 * Mantiene tu misma lógica de permisos, pero reutilizable.
 */
private function ensureContactAccess(Contact $contact, $baseQuery, int $created_by): void
{
    $is_my_contact = (clone $baseQuery)->where('created_by', $created_by)->exists();

    if ($is_my_contact) {
        return;
    }

    $is_admin = $this->contactUtil->is_admin(auth()->user());
    if ($is_admin) {
        return;
    }

    $assigned_to_user = $contact->userHavingAccess()
        ->wherePivot('user_id', $created_by)
        ->exists();

    if (!$assigned_to_user) {
        $contact->userHavingAccess()->attach($created_by);
    }
}

/**
 * cURL centralizado.
 */
private function callApiPeru(string $url, array $payload): array
{
    $curl = curl_init();

    curl_setopt_array($curl, array(
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_POSTFIELDS => json_encode($payload),
        CURLOPT_HTTPHEADER => [
            'Accept: application/json',
            'Content-Type: application/json',
            'Authorization: Bearer ' . config('services.apiperu.token'),
        ],
    ));

    $response = curl_exec($curl);
    $err = curl_error($curl);

    curl_close($curl);

    if ($err) {
        return [false, $err];
    }

    return [true, json_decode($response, true)];
}

/**
 * Mapper de data por tipo.
 */
private function createContactFromApi(string $type, array $data, int $business_id, int $created_by): Contact
{
    $common = [
        'business_id'     => $business_id,
        'type'            => 'customer',
        'contact_status'  => 'active',
        'mobile'          => '999999999',
        'email'           => 'ejemplo@gmail.com',
        'created_by'      => $created_by,
    ];

    if ($type === 'dni') {
        return new Contact($common + [
            'name'       => $data['nombre_completo'] ?? null,
            'first_name' => $data['nombres'] ?? null,
            'last_name'  => trim(($data['apellido_paterno'] ?? '') . ' ' . ($data['apellido_materno'] ?? '')),
            'contact_id' => $data['numero'] ?? null,
        ]);
    }

    // ruc
    return new Contact($common + [
        'supplier_business_name' => $data['nombre_o_razon_social'] ?? null,
        'contact_id'             => $data['ruc'] ?? null,
        'city'                   => $data['provincia'] ?? null,
        'state'                  => $data['departamento'] ?? null,
        'address_line_1'         => $data['direccion_completa'] ?? null,
    ]);
}
}
