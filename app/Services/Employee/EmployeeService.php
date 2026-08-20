<?php

namespace App\Services\Employee;

use App\Models\Employee;
use App\Repositories\Employee\EmployeeRepository;
use Illuminate\Validation\ValidationException;

class EmployeeService
{
    public function __construct(
        protected EmployeeRepository $employeeRepository
    ) {
    }

    public function index(int $employerId, array $filters) {
        return $this->employeeRepository
        ->paginateByEmployer(
            $employerId,
            $filters
        );
    }

    public function show(string $uuid, int $employerId): Employee {
        $employee = $this->employeeRepository
            ->findByUuidForEmployer(
                $uuid,
                $employerId
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

    public function store(
        int $employerId,
        array $data
    ): Employee {
        $employee = $this->employeeRepository->create([
            'employer_id'       => $employerId,
            'employee_no'       => $data['employee_no'],
            'first_name'        => $data['first_name'],
            'middle_name'      => $data['middle_name'] ?? null,
            'last_name'         => $data['last_name'],
            'email'             => $data['email'] ?? null,
            'mobile'            => $data['mobile'] ?? null,
            'position'          => $data['position'] ?? null,
            'department'        => $data['department'] ?? null,
            'pusopay_wallet_id' => $data['pusopay_wallet_id'],
            'status'            => $data['status'],
            'hired_at'          => $data['hired_at'] ?? null,
        ]);

        return $employee;
    }

    public function update(Employee $employee, array $data): Employee {
        $this->employeeRepository->update(
            $employee,
            $data
        );

        return $employee->refresh();
    }

    public function destroy(Employee $employee): void
    {
        $this->employeeRepository->update(
            $employee,
            [
                'status' => Employee::STATUS_INACTIVE,
            ]
        );

        $this->employeeRepository->delete($employee);
    }
}