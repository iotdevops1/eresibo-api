<?php
namespace App\Http\Controllers\Api\Employee;
use App\Http\Controllers\BaseApiController;
use App\Http\Resources\PayslipResource;
use App\Services\Payslip\PayslipService;
use Illuminate\Http\Request;
class PayslipAcknowledgementController extends BaseApiController {
    public function __construct(protected PayslipService $payslipService) {}
    public function store(Request $request,string $uuid) {
        $payslip=$this->payslipService->acknowledge($uuid,$request->user()->id);
        return $this->success(new PayslipResource($payslip),'Payslip acknowledged and net pay credited successfully.');
    }
}
