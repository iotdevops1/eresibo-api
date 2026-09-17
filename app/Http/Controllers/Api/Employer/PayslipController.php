<?php
namespace App\Http\Controllers\Api\Employer;
use App\Http\Controllers\BaseApiController;
use App\Http\Requests\Payslip\StorePayslipRequest;
use App\Http\Resources\PayslipResource;
use App\Services\Payslip\PayslipService;
class PayslipController extends BaseApiController {
    public function __construct(protected PayslipService $payslipService) {}
    public function store(StorePayslipRequest $request) {
        $payslip = $this->payslipService->create($request->user()->merchant_id,$request->user()->id,$request->validated());
        return $this->success(new PayslipResource($payslip),'Payslip issued successfully.',201);
    }
}
