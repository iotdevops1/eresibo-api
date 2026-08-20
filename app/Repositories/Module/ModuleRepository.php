<?php

namespace App\Repositories\Module;

use App\Models\Module;
use App\Repositories\BaseRepository;

class ModuleRepository extends BaseRepository
{
    public function __construct(Module $model) {
        $this->model = $model;
    }

    public function sidebar(array $permissions)
    {
        return $this->model
            ->newQuery()
            ->with([
                'children' => function ($query) use ($permissions) {
                    $query->where('active', true)
                        ->where(function ($q) use ($permissions) {
                            $q->whereNull('permission_code')
                            ->orWhereIn('permission_code', $permissions);
                        })
                        ->orderBy('sort_order');
                }
            ])
            ->whereNull('parent_id')
            ->where('active', true)
            ->where(function ($q) use ($permissions) {

                $q->where(function ($sub) use ($permissions) {
                    $sub->whereNotNull('permission_code')
                        ->whereIn('permission_code', $permissions);
                })

                ->orWhere(function ($sub) {
                    $sub->whereNull('permission_code');
                });
            })
            ->orderBy('sort_order')
            ->get()
            ->filter(function ($module) {

                // hide parent menus with no visible children
                if ($module->children->count() > 0) {
                    return true;
                }

                // keep normal standalone menu items
                return $module->route !== null;
            })
            ->values();
    }
}