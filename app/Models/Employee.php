<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\User;
use App\Models\Merchant;
use App\Models\PayrollBatchItem;

class Employee extends Model
{
    use HasUuids, SoftDeletes;

    protected $fillable = [
        'uuid',
        'merchant_id',
        'user_id',
        'employee_no',
        'first_name',
        'middle_name',
        'last_name',
        'email',
        'mobile',
        'position',
        'department',
        'pusopay_wallet_id',
        'status',
        'hired_at',
        'terminated_at',
    ];

    protected $casts = [
        'status' => 'integer',
        'hired_at' => 'date',
        'terminated_at' => 'date',
    ];

    public const STATUS_ACTIVE = 1;
    public const STATUS_INACTIVE = 2;
    public const STATUS_SUSPENDED = 3;
    public const STATUS_TERMINATED = 4;

    public function uniqueIds(): array
    {
        return ['uuid'];
    }

    public function getFullNameAttribute(): string
    {
        return trim(
            implode(' ', array_filter([
                $this->first_name,
                $this->middle_name,
                $this->last_name,
            ]))
        );
    }

    public function payrollBatchItems()
    {
        return $this->hasMany(
            PayrollBatchItem::class,
            'employee_id'
        );
    }

    public function merchant()
    {
        return $this->belongsTo(
            Merchant::class,
            'merchant_id'
        );
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}