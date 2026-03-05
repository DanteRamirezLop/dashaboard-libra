<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Utils\NotificationUtil;
use App\Business;
use App\Loan;
use App\PaymentSchedule;
use App\Delay;
use App\Transaction;
use Carbon\Carbon;

class MsmNotification extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'pos:smsPagoVencer';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Envio de mensaje sms a los clientes que esta su cuenta por vencer';

    /**
     * Execute the console command.
     *
     * @return int
     */

    public function __construct(NotificationUtil $notificationUtil)
    {
        parent::__construct();
        $this->notificationUtil = $notificationUtil;
    }

    public function handle()
    {
        try {
            //Crea una nueva mora y cambia el estado de la letra y del prestamo
            DB::beginTransaction();
                $numeros = [];
                $customers = [];
                $dayRightNow = Carbon::now();
                $primerDiaMes = Carbon::now()->startOfMonth()->startOfDay()->toDateString();
                $ultimoDiaMes = Carbon::now()->endOfMonth()->toDateString();
                $loans = Loan::whereIn('status', ['approved', 'in arrears', 'partial'])->get();

                foreach ($loans as $loan) {
                    // 1) ¿Existe alguna versión activa para este loan?
                    $hasActiveVersion = DB::table('payment_schedules as psx')
                        ->join('schedule_versions as svx', 'svx.id', '=', 'psx.schedule_version_id')
                        ->where('psx.loan_id', $loan->id)
                        ->where('svx.status', 'active')
                        ->exists();

                    // 2) Traer SOLO el cronograma correcto (activo si existe; si no, el original NULL)
                    $payment_schedules = PaymentSchedule::query()
                        ->from('payment_schedules as ps')
                        ->leftJoin('schedule_versions as sv', 'sv.id', '=', 'ps.schedule_version_id')
                        ->where('ps.loan_id', $loan->id)
                        ->where('ps.status', '!=', 'paid')
                        ->whereBetween('ps.sheduled_date', [$primerDiaMes, $ultimoDiaMes])
                        ->when(
                            $hasActiveVersion,
                            fn ($q) => $q->where('sv.status', 'active'),
                            fn ($q) => $q->whereNull('ps.schedule_version_id')
                        )
                        // OJO: como haces join + from(alias), selecciona las columnas del modelo
                        ->select('ps.*')
                        // opcional: define un orden para el "first" (para que sea determinista)
                        ->orderBy('ps.sheduled_date', 'asc')
                        ->first();

                    if ($payment_schedules) {
                        $payday =  Carbon::parse($payment_schedules->sheduled_date);//Fecha de pago
                        $days_late = $payday->diffInDays($dayRightNow, false); //Dias atrasado
                        # 2 DIAS DE ANTICIPACION
                        if( $days_late == -2 || $days_late == -1 || $days_late == 0){ 
                            if($payment_schedules->status != 'paid'){
                                if (!empty($loan->contact->mobile)) {
                                    $numeros[] = $loan->contact->mobile;
                                }
                            }
                        }
                        // 1 Dia atrasado - Crear el registro de la primera mora 
                        if($days_late == 1){
                            $late_amount_late = ($payment_schedules->mount_quota + $payment_schedules->initial) * 0.00111;
                            $late_amount = $late_amount_late ; //Calcular la cantidad de morosidad
                            //registro de la mora en el primer día
                            Delay::create(['late_date'=> $dayRightNow,'days_late'=> $days_late,'late_amount'=> $late_amount,'status'=> 'late','regularization_date'=> null,'loan_id'=> $loan->id,'payment_schedule_id'=>$payment_schedules->id]);
                            //Aumento la cantidad de morosidad en el la registro total
                            $transaction = Transaction::find($loan->transaction_id);
                            $transaction->final_total +=  $late_amount;
                            $transaction->additional_expense_value_2 += $late_amount;
                            $transaction->save();
                            #------CAMBIO DE ESTADO A LA LETRA EN MORA-------------
                            $payment_schedules->status = 'overdue';
                            $payment_schedules->save();
                            #-----CAMBIO EL ESTADO DEL PRESTAMO EN MORA------------
                            $loan->status = 'in arrears';
                            $loan->save(); 
                        }
                    }
                }

                // if($numeros){ 
                //     $phones = array_unique($numeros);
                //     $mobile_number = implode(',', $phones);
                //     #Envio de mensaje SMS a los clientes
                //     try {
                //         $data = [];
                //         $business = Business::find(4);//Codifo de la empresa en duro
                //         $data['sms_settings'] = $business->sms_settings ?? [];
                //         $data['mobile_number'] = $mobile_number;
                //         $data['sms_body'] = "Hola! XCMG Libra International te recuerda, el pago de tu préstamo está proximo a vencer.";
                //         $this->notificationUtil->sendSms($data);
                //     } catch (\Exception $e) {
                //         \Log::emergency('File:'.$e->getFile().'Line:'.$e->getLine().'Message:'.$e->getMessage());
                //     }
                // }
            DB::commit();
            Log::info('JOB de SMS se ejecuto correctamente el '. now());
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::emergency('Command MsmNotification File:'.$e->getFile().'Line:'.$e->getLine().'Message:'.$e->getMessage());
            exit($e->getMessage());
        }
    }
}
