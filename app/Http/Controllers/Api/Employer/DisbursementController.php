<?php
namespace App\Http\Controllers\Api\Employer;
use App\Http\Controllers\BaseApiController;
use App\Http\Requests\Disbursement\DisbursementFilterRequest;
use App\Http\Requests\Disbursement\StoreDisbursementRequest;
use App\Http\Resources\DisbursementResource;
use App\Services\Disbursement\DisbursementService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
class DisbursementController extends BaseApiController {
 public function __construct(private DisbursementService $service) {}
 public function index(DisbursementFilterRequest $request):JsonResponse{return $this->success(DisbursementResource::collection($this->service->index($request->user(),$request->validated())),'Disbursements retrieved successfully.');}
 public function store(StoreDisbursementRequest $request):JsonResponse{return $this->success(new DisbursementResource($this->service->create($request->user(),$request->validated())),'Disbursement created and approved successfully.',201);}
 public function show(Request $request,string $uuid):JsonResponse{return $this->success(new DisbursementResource($this->service->show($request->user(),$uuid)),'Disbursement retrieved successfully.');}
 public function release(Request $request,string $uuid):JsonResponse{return $this->success(new DisbursementResource($this->service->release($request->user(),$uuid)),'Disbursement released successfully.');}
 public function cancel(Request $request,string $uuid):JsonResponse{return $this->success(new DisbursementResource($this->service->cancel($request->user(),$uuid)),'Disbursement cancelled successfully.');}
}
