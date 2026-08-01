<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class UserRole extends Model
{
    use HasUuids, SoftDeletes;

    protected $table = 'user_roles';

    protected $fillable = [
        'uuid',
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

    public function users()
    {
        return $this->hasMany(User::class, 'role_id');
    }
}