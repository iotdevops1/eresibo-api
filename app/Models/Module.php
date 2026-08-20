<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Module extends Model
{
    use HasUuids;
    use SoftDeletes;

    protected $fillable = [
        'uuid',
        'code',
        'name',
        'icon',
        'route',
        'permission_code',
        'parent_id',
        'sort_order',
        'is_menu',
        'active',
    ];

    public function uniqueIds(): array
    {
        return ['uuid'];
    }

    public function parent()
    {
        return $this->belongsTo(Module::class);
    }

    public function children()
    {
        return $this->hasMany(
            Module::class,
            'parent_id'
        )->orderBy('sort_order');
    }
}