<?php

namespace App\Services\Employee;

use App\Models\Employee;
use App\Models\UserRole;
use App\Models\User;
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

   /**
     * Create Employee
     */
    public function store(int $merchantId, array $data): Employee
    {
        return DB::transaction(function () use ($merchantId, $data) {

            /*
            |--------------------------------------------------------------------------
            | Get EMPLOYEE role
            |--------------------------------------------------------------------------
            */

            $employeeRole = UserRole::query()
                ->where('code', 'EMPLOYEE')
                ->first();

            if (! $employeeRole) {
                throw ValidationException::withMessages([
                    'role' => [
                        'EMPLOYEE role is not configured.',
                    ],
                ]);
            }

            /*
            |--------------------------------------------------------------------------
            | Create employee login account
            |--------------------------------------------------------------------------
            */

            $employeeUser = User::create([
                'name' => trim(
                    $data['first_name']
                    . ' '
                    . ($data['middle_name'] ?? '')
                    . ' '
                    . $data['last_name']
                ),
                'email' => $data['email'],
                'mobile' => $data['mobile'] ?? null,
                'password' => $data['temporaryPassword'],
                'merchant_id' => $merchantId,
                'role_id' => $employeeRole->id,
                'status' => User::STATUS_ACTIVE,
                'must_change_password' => true,
            ]);

            /*
            |--------------------------------------------------------------------------
            | Create employee profile
            |--------------------------------------------------------------------------
            */

            return $this->employeeRepository->create([
                'merchant_id' => $merchantId,

                /*
                * Link Employee to the login account.
                */
                'user_id' => $employeeUser->id,

                'employee_no' => $data['employee_no'],

                'first_name' => $data['first_name'],

                'middle_name' => $data['middle_name'] ?? null,

                'last_name' => $data['last_name'],

                'email' => $data['email'] ?? null,

                'mobile' => $data['mobile'] ?? null,

                'position' => $data['position'] ?? null,

                'department' => $data['department'] ?? null,

                'pusopay_wallet_id' => $data['pusopay_wallet_id'],

                'status' => $data['status'],

                'hired_at' => $data['hired_at'] ?? null,
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