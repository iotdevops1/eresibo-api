<?php
namespace App\Http\Controllers\Api\Employer;
use App\Http\Controllers\BaseApiController;
use App\Http\Requests\FundHold\FundHoldFilterRequest;
use App\Http\Requests\FundHold\StoreFundHoldRequest;
use App\Http\Resources\FundHoldResource;
use App\Services\FundHold\FundHoldService;
use Illuminate\Http\Request;
class FundHoldController extends BaseApiController {
 public function __construct(private FundHoldService $service){}
 public function index(FundHoldFilterRequest $r){return $this->success(FundHoldResource::collection($this->service->index($r->user(),$r->validated())),'Fund holds retrieved successfully.');}
 public function summary(Request $r){return $this->success($this->service->summary($r->user()),'Fund hold summary retrieved successfully.');}
 public function store(StoreFundHoldRequest $r){return $this->success(new FundHoldResource($this->service->create($r->user(),$r->validated())),'Fund hold created successfully.',201);}
 public function show(Request $r,string $uuid){return $this->success(new FundHoldResource($this->service->show($r->user(),$uuid)),'Fund hold retrieved successfully.');}
 public function release(Request $r,string $uuid){return $this->success(new FundHoldResource($this->service->release($r->user(),$uuid)),'Held funds released successfully.');}
 public function return(Request $r,string $uuid){return $this->success(new FundHoldResource($this->service->return($r->user(),$uuid)),'Held funds returned successfully.');}
 public function override(Request $r,string $uuid){return $this->success(new FundHoldResource($this->service->override($r->user(),$uuid)),'Fund hold overridden successfully.');}
}
