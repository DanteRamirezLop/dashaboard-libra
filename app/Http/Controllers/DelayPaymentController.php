<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\PaymentSchedule;
use App\Utils\TransactionUtil;
use App\Loan;
use App\Transaction;
use App\TransactionPayment;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Contact;
use App\Delay;
use App\AccountTransaction;
use App\Business;

class DelayPaymentController extends Controller
{
     /**
     * All Utils instance.
     */
    protected $transactionUtil;
    /**
     * Create a new controller instance.
     *
     * @param  ProductUtils  $product
     * @return void
     */
   public function __construct(TransactionUtil $transactionUtil)
    {
        $this->transactionUtil = $transactionUtil;
    }

    public function store(Request $request){

        try {
            $note = 'Pago moratorio. ';
            $delay = Delay::find($request->delay_id);
            $amount_delay =  $delay->late_amount;
            $amount =  (float) $request->amount;
            $type = $request->type_pay;

            //El monto a pagar no puede ser superior a la cuota 
            if( round($amount,2) <= round($amount_delay,2) ){

                    DB::beginTransaction();

                    // metodo de pago
                    $account_id = null;
                    if(! empty($request->input('account_id'))){
                        $account_id = $request->input('account_id');
                    }
                    //Si es Soles
                    if($request->currency !='Dolar'){
                        $note .= $request->amount_var.' '. $request->currency.' con tipo de cambio '. $request->exchange_rate.'. ';
                    }
                    //fecha de pago
                    if($request->paid_on){
                        $paid_on = $this->transactionUtil->uf_date($request->input('paid_on'), true);
                    }else{
                        $rightNow = Carbon::now();
                        $paid_on = $rightNow->toDateTimeString();
                    }
                    //Crear el pagon de la cuota
                    $loan = Loan::find($delay->loan_id);
                    $transaction = Transaction::find($loan->transaction_id);

                    //Ver que 
                    if($type == 'pay'){
                        $delay->status = 'regularized';
                        $delay->save();
                    }else{
            
                        
                        $amount_condone =  round($amount_delay - $amount,4);
                         
                        $note .= ' Se está condonado $'.round($amount_condone,2).' de $'.round($amount_delay,2);
                        //FALTA VALIDAR QUE CONDONAR PARCIAL TIENE QUE SER MENOR AL MONTO TOTAL DE LA DEUDA
                        $transaction->final_total -=  $amount_condone;
                        $transaction->additional_expense_value_2 -= $amount_condone;
                        $transaction->save();
                        //cambio de estado en la mora
                        $delay->status = 'partial';
                        $delay->save();  
                    }

                    $note .= ' .'.$request->note;

                    $transactionPaymentNew =  $this->transactionUtil->newTransaction(
                        $transaction, 
                        $amount, 
                        $loan->user_id, 
                        $loan->customer_id, 
                        $note, 
                        $paid_on, 
                        $request->method,
                        null, 
                        $account_id,
                        $delay->id,
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

                DB::commit();

                $output = ['success' => true,
                    'msg' => __('purchase.payment_added_success').$type,
                ];
            }else{
                $msg = __('El pago no puede ser mayor al monto moratorio');
                $output = ['success' => false,
                    'msg' => $msg,
                ];
            }

        } catch (\Exception $e) {
            DB::rollBack();
            $msg = __('messages.something_went_wrong');
            $output = ['success' => false,
                'msg' => $msg,
            ];
        }
        return redirect()->back()->with(['status' => $output]);
    }
    
}
