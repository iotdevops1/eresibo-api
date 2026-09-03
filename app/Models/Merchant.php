<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Merchant extends Model
{
    use HasUuids, SoftDeletes;

    protected $fillable = [
        'uuid',
        'merchant_code',
        'business_name',
        'business_type',
        'email',
        'mobile',
        'address',
        'status',
    ];

    protected $casts = [
        'status' => 'integer',
    ];

    public const STATUS_ACTIVE = 1;
    public const STATUS_INACTIVE = 2;
    public const STATUS_SUSPENDED = 3;

    public function uniqueIds(): array
    {
        return ['uuid'];
    }

    public function employers()
    {
        return $this->hasMany(
            User::class,
            'merchant_id'
        );
    }
    
    public function employees()
    {
        return $this->hasMany(
            Employee::class,
            'merchant_id'
        );
    }
}