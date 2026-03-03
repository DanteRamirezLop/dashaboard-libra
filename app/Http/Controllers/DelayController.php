<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Delay;
use App\Loan;
use App\Contact;
use App\PaymentSchedule;
use App\Utils\ContactUtil;
use App\Utils\ModuleUtil;
use App\Utils\NotificationUtil;
use App\Utils\TransactionUtil;
use App\Utils\Util;
use Carbon\Carbon;
use App\Transaction;
use Illuminate\Support\Facades\DB;

class DelayController extends Controller
{
    protected $commonUtil;
    protected $contactUtil;
    protected $transactionUtil;
    protected $moduleUtil;
    protected $notificationUtil;
    /**
     * Constructor
     *
     * @param  Util  $commonUtil
     * @return void
     */
    public function __construct(
        Util $commonUtil,
        ModuleUtil $moduleUtil,
        TransactionUtil $transactionUtil,
        NotificationUtil $notificationUtil,
        ContactUtil $contactUtil
    ) {
        $this->commonUtil = $commonUtil;
        $this->contactUtil = $contactUtil;
        $this->moduleUtil = $moduleUtil;
        $this->transactionUtil = $transactionUtil;
        $this->notificationUtil = $notificationUtil;
    }

    public function index(){

    }

    
    public function show($id){
        $payment_schedule = PaymentSchedule::find($id);
        $loan =  $payment_schedule->loan;
        $delay = $payment_schedule->delay;
        $customer = Contact::find($loan->customer_id);
        // $late_date = Carbon::parse($delay->late_date);
        // $paid_on = $late_date->addDays($delay->days_late);
        return view('loan.delay.show',compact('delay','payment_schedule','customer','loan'));
    }
    

    public function create(){

    }

   public function store(Request $request){
        try {
            DB::beginTransaction();

            $payment_schedules = PaymentSchedule::findOrFail($request->payment_schedule_id);
            $loan = Loan::findOrFail($request->loan_id);
            $days_late =  $request->days_late;
            
                $sheduled_date = Carbon::parse($payment_schedules->sheduled_date);
                $start_date_var =  Carbon::parse($payment_schedules->sheduled_date);
                $start_date = $start_date_var->addDays(1);
                $late_date =  $sheduled_date->addDays($days_late);
                $late_amount = (($payment_schedules->mount_quota + $payment_schedules->initial) * 0.00111) * $days_late;
                #------------------
                Delay::create([
                'start_date'=> $start_date,
                'late_date'=> $late_date,
                'days_late'=> $days_late,
                'late_amount'=> $late_amount,
                'status'=> 'late',
                'regularization_date'=> null,
                'loan_id'=> $request->loan_id,
                'payment_schedule_id'=>$request->payment_schedule_id]);
                #------------------
                $transaction = Transaction::findOrFail($loan->transaction_id);
                $transaction->final_total +=  $late_amount;
                $transaction->additional_expense_value_2 += $late_amount;
                $transaction->save();
                #------------------
                $msg = ['success' => true,'msg' => __('Registrado')];
            #------------------
            
            DB::commit();
            $output = $msg;
        } catch (\Exception $e) {
            DB::rollBack();
            $msg = __('messages.something_went_wrong');
            $output = ['success' => false,'msg' => $msg.$e->getMessage(),];
        }
        return redirect()->back()->with(['status' => $output]);
    }

    public function addDelay($id){
        // if (! auth()->user()->can('brand.create')) {
        //     abort(403, 'Unauthorized action.');
        // }
        $payment_schedule = PaymentSchedule::find($id);
        $loan =  $payment_schedule->loan;
        $late_date = Carbon::now()->toDateTimeString();
        return view('loan.delay.create_modal')->with(compact('late_date','payment_schedule','loan'));
    }

    //FALTA CAMBIAR LA ELIMINACION POR UNA ELIMINACION LOGICA
    public function destroy($id){ 
        // if (! auth()->user()->can('brand.delete')) {
        //     abort(403, 'Unauthorized action.');
        // }
        if (request()->ajax()) {
            try {
                //Buscar la deuda o Genere error 
                $delay = Delay::findOrFail($id);
                $loan = Loan::findOrFail($delay->loan_id);
                //Restar la cantidad de morosidad en el la registro total
                $transaction = Transaction::findOrFail($loan->transaction_id);
                if($transaction->additional_expense_value_2 > 0.00){
                    $transaction->final_total -=  $delay->late_amount;
                    $transaction->additional_expense_value_2 -=  $delay->late_amount;
                    $transaction->save();
                }
                $delay->delete();
                $output = ['success' => true,
                    'msg' => 'Deuda eliminado con exito.',
                ];
            } catch (\Exception $e) {
                \Log::emergency('File:'.$e->getFile().'Line:'.$e->getLine().'Message:'.$e->getMessage());

                $output = ['success' => false,
                    'msg' => __('messages.something_went_wrong'),
                ];
            }
            return $output;
        }
    }

    public function condonar(Request $request){
        try {
            //Buscar la deuda o Genere error 
            $delay = Delay::findOrFail($request->id);
            $delay->status = 'condone';
            $delay->save();
            $loan = Loan::findOrFail($delay->loan_id);
            //Restar la cantidad de morosidad en el la registro total
            $transaction = Transaction::findOrFail($loan->transaction_id);
            if($transaction->additional_expense_value_2 > 0.00){
                $transaction->final_total -=  $delay->late_amount;
                $transaction->additional_expense_value_2 -=  $delay->late_amount;
                $transaction->save();
            }
            $output = ['success' => true,
                'msg' => 'Deuda condonada.'.$delay->id,
            ];
        } catch (\Exception $e) {
            \Log::emergency('File:'.$e->getFile().'Line:'.$e->getLine().'Message:'.$e->getMessage());

            $output = ['success' => false,
                'msg' => __('messages.something_went_wrong'),
            ];
        }
        return $output;
    }

   public function addPayment($delay_id){
        if (request()->ajax()) {
            $delay = Delay::findOrFail($delay_id);
            if ($delay->status != 'regularized') {
                $business_id = request()->session()->get('user.business_id');
                $accounts = $this->moduleUtil->accountsDropdown($business_id, true, false, true);
                $payment_types = $this->transactionUtil->payment_types(null, false, $business_id);
                $amount =  $delay->late_amount;
                $paid_on = Carbon::now()->toDateTimeString();
                $view = view('loan.delay.pay_delay_row')->with(compact('delay','amount','paid_on','payment_types','accounts'))->render();
                $output = ['status' => 'due','view' => $view, ];
            } else {
                $output = ['status' => 'paid','view' => '','msg' => __('purchase.amount_already_paid'),  ];
            }
            return json_encode($output);
        }
    }
}
