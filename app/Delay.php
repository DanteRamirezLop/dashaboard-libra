<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Scopes\ExcludeDeleted;

class Delay extends Model
{
    use HasFactory,SoftDeletes;

    protected $fillable = [
        'start_date',
        'late_date',
        'days_late',
        'late_amount',
        'status',
        'regularization_date',
        'loan_id',
        'payment_schedule_id' 
    ];
    
    protected static function booted()
    {
        static::addGlobalScope('not_deleted', function ($query) {
            $query->whereNull('deleted_at');
        });
    }

    public function loans(){
        return $this->hasMany(Loan::class);
    }

    public function paymentShedule(){
        return $this->belongsTo(PaymentShedule::class);
    }
    
    public function paymentSchedule(){
        return $this->belongsTo(PaymentSchedule::class);
    }

}
