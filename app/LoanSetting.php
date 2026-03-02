<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Permission\PermissionRegistrar;
use Spatie\Permission\Exceptions\RoleAlreadyExists;
use Spatie\Permission\Guard;

class LoanSetting extends Model
{
    use HasFactory;
    protected $table = 'loan_settings';

    protected $fillable = [
        'user_id',
        'name',
        'amount_total',
        'amount_inicial',
        'description'
    ];

    public function __construct(array $attributes = [])
    {
        $attributes['guard_name'] = $attributes['guard_name'] ?? config('auth.defaults.guard');
        parent::__construct($attributes);
        $this->guarded[] = $this->primaryKey;
    }

    // public function getTable()
    // {
    //     return config('permission.table_names.goals', parent::getTable());
    // }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
