<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Permission extends Model
{
    use HasUuids, SoftDeletes;

    protected $fillable = [
        'uuid',
        'module',
        'code',
        'name',
        'description',
        'active',
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

    public function roles()
    {
        return $this->belongsToMany(
            UserRole::class,
            'user_role_permissions',
            'permission_id',
            'role_id'
        );
    }
}