<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class UserRole extends Model
{
    public const STATUS_ACTIVE    = 1;
    public const STATUS_INACTIVE  = 2;
    public const STATUS_SUSPENDED = 3;
    public const STATUS_LOCKED    = 4;
    public const STATUS_DELETED   = 5;

    use HasUuids, SoftDeletes;

    protected $table = 'user_roles';

    protected $fillable = [
        'uuid',
        'code',
        'name',
        'description',
        'active',
        'status',
    ];

    protected $hidden = [
        'id',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    public function uniqueIds(): array
    {
        return ['uuid'];
    }

    public function users()
    {
        return $this->hasMany(User::class, 'role_id');
    }

    public function permissions()
    {
        return $this->belongsToMany(
            Permission::class,
            'user_role_permissions',
            'role_id',
            'permission_id'
        );
    }
}