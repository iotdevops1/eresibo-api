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
    use HasApiTokens, HasFactory, Notifiable, HasUuids, SoftDeletes;

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
    ];

    protected $hidden = [
        'id',
        'role_id',
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'last_login_at'      => 'datetime',
        'password'           => 'hashed',
        'is_login'           => 'boolean',
        'is_lock'            => 'boolean',
    ];

    public function uniqueIds(): array
    {
        return ['uuid'];
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    public function role()
    {
        return $this->belongsTo(UserRole::class, 'role_id');
    }
}