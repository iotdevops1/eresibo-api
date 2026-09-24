<?php
namespace App\Http\Controllers\Api\Employer;
use App\Http\Controllers\BaseApiController;
use App\Http\Requests\Report\PayrollReportRequest;
use App\Services\Report\EmployerPayrollReportService;
use App\Services\Report\PayrollReportExportService;
class ReportController extends BaseApiController {
 public function __construct(protected EmployerPayrollReportService $reports,protected PayrollReportExportService $exports){}
 public function summary(PayrollReportRequest $request){return $this->success($this->reports->report($request->user(),$request->validated()),'Payroll report retrieved successfully.');}
 public function export(PayrollReportRequest $request){return $this->exports->download($this->reports->report($request->user(),$request->validated()),$request->validated('format','xlsx'));}
}
