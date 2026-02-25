<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentApplication extends Model
{
    use HasFactory;
    protected $table = 'payment_applications';
    protected $fillable = [
        'loan_id',
        'transaction_payment_id',
        'transaction_id',
        'concept',
        'amount',
        'amount_discounted',
        'applied_at',
        'days_in_advance',
        'payment_schedule_id',
    ];

    public function loan()
    {
        return $this->belongsTo(Loan::class);
    }

    public function transaction(){
        return $this->belongsTo(Transaction::class);
    }

    public function paymentSchedule(){
        return $this->belongsTo(PaymentSchedule::class);
    }
}
