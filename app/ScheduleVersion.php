<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ScheduleVersion extends Model
{
    use HasFactory;
    protected $table = 'schedule_versions';
    protected $fillable = [
        'loan_id',
        'transaction_payment_id',
        'status',
        'reason',
        'generated_at'
    ];

    public function loan(){
        return $this->belongsTo(Loan::class);
    }

    public function paymentSchedules(){
        return $this->hasMany(PaymentSchedule::class);
    }

    public function transaction(){
        return $this->belongsTo(Transaction::class);
    }
}
