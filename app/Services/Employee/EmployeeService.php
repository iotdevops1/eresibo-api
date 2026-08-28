<?php

namespace App\Services\Employee;

use App\Models\Employee;
use App\Repositories\Employee\EmployeeRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class EmployeeService
{
    public function __construct(
        protected EmployeeRepository $employeeRepository
    ) {
    }

    /*
    |--------------------------------------------------------------------------
    | List Employees
    |--------------------------------------------------------------------------
    */

    public function index(int $merchantId, array $filters = []) {
        return $this->employeeRepository
            ->paginateByMerchant(
                $merchantId,
                $filters
            );
    }

    /*
    |--------------------------------------------------------------------------
    | Show Employee
    |--------------------------------------------------------------------------
    */

    public function show(string $uuid, int $merchantId): Employee {
        $employee = $this->employeeRepository
            ->findByUuidForMerchant(
                $uuid,
                $merchantId
            );

        if (! $employee) {
            throw ValidationException::withMessages([
                'employee' => [
                    'Employee not found.'
                ],
            ]);
        }

        return $employee;
    }

    /*
    |--------------------------------------------------------------------------
    | Create Employee
    |--------------------------------------------------------------------------
    */

    public function store(int $merchantId, array $data): Employee {
        return DB::transaction(function () use ($merchantId, $data) {

            return $this->employeeRepository->create([
                'merchant_id'       => $merchantId,
                'employee_no'       => $data['employee_no'],
                'first_name'        => $data['first_name'],
                'middle_name'       => $data['middle_name'] ?? null,
                'last_name'         => $data['last_name'],
                'email'             => $data['email'] ?? null,
                'mobile'            => $data['mobile'] ?? null,
                'position'          => $data['position'] ?? null,
                'department'        => $data['department'] ?? null,
                'pusopay_wallet_id' => $data['pusopay_wallet_id'],
                'status'            => $data['status'],
                'hired_at'          => $data['hired_at'] ?? null,
            ]);
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Update Employee
    |--------------------------------------------------------------------------
    */

    public function update(Employee $employee, array $data): Employee {
        $this->employeeRepository->update(
            $employee,
            $data
        );

        return $employee->refresh();
    }

    /*
    |--------------------------------------------------------------------------
    | Delete Employee
    |--------------------------------------------------------------------------
    */

    public function destroy(Employee $employee): void {
        DB::transaction(function () use ($employee) {
            $this->employeeRepository->update($employee, [
                    'status' => Employee::STATUS_INACTIVE,
                ]
            );

            $this->employeeRepository->delete(
                $employee
            );
        });
    }
}