<?php
namespace App\Services\Payslip;

use App\Models\Payslip;
use App\Repositories\Payslip\PayslipRepository;
use Illuminate\Validation\ValidationException;

class EmployeePayslipService {
    public function __construct(protected PayslipRepository $payslipRepository) {}

    public function index(int $userId,array $filters=[]) {
        return $this->payslipRepository->paginateByEmployeeUser($userId,$filters);
    }

    public function show(string $uuid,int $userId): Payslip {
        $payslip=$this->payslipRepository->findByUuidForEmployeeUser($uuid,$userId);
        if(! $payslip) throw ValidationException::withMessages([
            'payslip'=>['Payslip not found.'],
        ]);

        return $payslip;
    }
}
