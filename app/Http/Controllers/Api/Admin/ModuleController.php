<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\BaseApiController;
use App\Http\Resources\ModuleResource;
use App\Services\Module\ModuleService;

class ModuleController extends BaseApiController
{
    public function __construct(
        protected ModuleService $moduleService
    ) {
    }

    public function sidebar()
    {
        return $this->success([
            'menus' => ModuleResource::collection(
                $this->moduleService->sidebar()
            )],
            'Sidebar menus retrieved successfully.'
        );
    }
}