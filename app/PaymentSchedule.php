<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\TransactionPayment;

class PaymentSchedule extends Model
{
    use HasFactory;
    // status = ['pending', 'paid', 'overdue','partial']
    protected $fillable = [
        'loan_id',
        'number_quota',
        'sheduled_date',
        'mount_quota',
        'status',
        'opening_balance',
        'capital',
        'interests',
        'final_balance',
        'gps_quota',
        'sure_quota',
        'admin_fee_quota',  
        'number_letter',
        'initial',
        'shedule_version_id',
        'ref_payment_schedule_id',
    ];

     public function loan()
    {
        return $this->belongsTo(Loan::class);
    }

    // public function loanPayment(){
    //     return $this->hasMany(LoanPayment::class);
    // }
    
    public function delay(){
        return $this->hasOne(Delay::class);
    }
    
     public function getQuote(){
       return $this->mount_quota + $this->gps_quota +  $this->sure_quota + $this->initial +$this->admin_fee_quota;
    }

    public function getLoanStatus()
    {
        $payment_shedule_status =  $this->status;
        $status = '';
        if ($payment_shedule_status == 'overdue') {
            $transactionPayments = TransactionPayment::where('payment_schedule_id', $this->id)->get();
            if($transactionPayments->isNotEmpty()){
                 $payment_shedule_status = 'partial-overdue';
            }
        }
        switch ($payment_shedule_status) {
            case 'partial-overdue':
                 $status =  '<span class="label label-danger">Atrasado Parcial</span>';
                break;
            case 'pending':
                $status = '<span class="label label-default">Pendiente</span>';
                break;
            case 'overdue':
                $status =  '<span class="label label-danger">Atrasado</span>';
                break;
            case 'paid':
                $status = '<span class="label label-success">Pagado</span>';
                break;
            case 'partial':
               $status = '<span class="label label-info">parcial</span>';
                break;
        }

        return $status;
    }
    
}
