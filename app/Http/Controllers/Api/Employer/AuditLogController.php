<?php
namespace App\Http\Controllers\Api\Employer;
use App\Http\Controllers\BaseApiController;
use App\Http\Requests\AuditLog\AuditLogFilterRequest;
use App\Http\Resources\AuditLogResource;
use App\Services\Audit\AuditLogService;
class AuditLogController extends BaseApiController {public function __construct(private AuditLogService $service){}public function index(AuditLogFilterRequest $r){return $this->success(AuditLogResource::collection($this->service->index($r->user(),$r->validated())),'Audit logs retrieved successfully.');}}
