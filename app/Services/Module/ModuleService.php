<?php

namespace App\Services\Module;

use App\Repositories\Module\ModuleRepository;

class ModuleService
{
    public function __construct(
        protected ModuleRepository $moduleRepository
    ) {
    }

    public function sidebar()
    {
        $user = auth()->user();

        $permissions = $user->role
            ->permissions
            ->pluck('code')
            ->toArray();

        return $this->moduleRepository
            ->sidebar($permissions);
    }
}