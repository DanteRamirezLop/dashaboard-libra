<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentSchedule extends Model
{
    use HasFactory;
    // status = ['pending', 'paid', 'overdue']
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

    public function loanPayment(){
        return $this->hasMany(LoanPayment::class);
    }
    
    public function delay(){
        return $this->hasOne(Delay::class);
    }
    
     public function getQuote(){
       return $this->mount_quota + $this->gps_quota +  $this->sure_quota + $this->initial +$this->admin_fee_quota;
    }
    
    
}
