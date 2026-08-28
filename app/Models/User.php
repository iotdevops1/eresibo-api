<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    public const STATUS_ACTIVE = 1;
    public const STATUS_INACTIVE = 2;
    public const STATUS_SUSPENDED = 3;
    public const STATUS_LOCKED = 4;
    public const STATUS_DELETED = 5;

    use HasApiTokens;
    use HasFactory;
    use Notifiable;
    use HasUuids;
    use SoftDeletes;

    protected $fillable = [
        'uuid',
        'role_id',
        'name',
        'email',
        'mobile',
        'password',
        'status',
        'is_login',
        'login_attempt',
        'is_lock',
        'last_login_at',
        'last_login_ip',
        'merchant_id',
    ];

    protected $hidden = [
        'id',
        'role_id',
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'last_login_at' => 'datetime',
        'password' => 'hashed',
        'is_login' => 'boolean',
        'is_lock' => 'boolean',
    ];

    public function uniqueIds(): array
    {
        return ['uuid'];
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    /*
    |--------------------------------------------------------------------------
    | Role
    |--------------------------------------------------------------------------
    */

    public function role()
    {
        return $this->belongsTo(
            UserRole::class,
            'role_id'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Merchant
    |--------------------------------------------------------------------------
    */

    public function merchant()
    {
        return $this->belongsTo(
            Merchant::class,
            'merchant_id'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Status
    |--------------------------------------------------------------------------
    */

    public function getStatusNameAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_ACTIVE => 'ACTIVE',
            self::STATUS_INACTIVE => 'INACTIVE',
            self::STATUS_SUSPENDED => 'SUSPENDED',
            self::STATUS_LOCKED => 'LOCKED',
            self::STATUS_DELETED => 'DELETED',
            default => 'UNKNOWN',
        };
    }

    /*
    |--------------------------------------------------------------------------
    | Employer Helper
    |--------------------------------------------------------------------------
    */

    public function isEmployer(): bool
    {
        return $this->role?->code === 'EMPLOYER';
    }
}