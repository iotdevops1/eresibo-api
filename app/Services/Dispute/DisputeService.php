<?php

namespace App\Services\Dispute;

use App\Models\Dispute;
use App\Models\Employee;
use App\Models\Payslip;
use App\Models\User;
use App\Repositories\Dispute\DisputeRepository;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class DisputeService
{
    public function __construct(protected DisputeRepository $disputeRepository)
    {
    }

    public function employeeIndex(User $user, array $filters)
    {
        return $this->disputeRepository->paginateForEmployeeUser($user->id, $filters);
    }

    public function employeeShow(User $user, string $uuid): Dispute
    {
        return $this->findEmployeeDispute($user, $uuid);
    }

    public function employeeStore(User $user, array $data): Dispute
    {
        $employee = $this->employeeForUser($user);
        $payslip = $this->employeePayslip($employee, $data['payslip_uuid'] ?? null);

        return Dispute::create([
            'merchant_id' => $employee->merchant_id,
            'reference' => $this->newReference(),
            'employee_id' => $employee->id,
            'payslip_id' => $payslip?->id,
            'created_by_user_id' => $user->id,
            'subject' => $data['subject'],
            'description' => $data['description'],
            'priority' => $data['priority'] ?? Dispute::PRIORITY_NORMAL,
            'status' => Dispute::STATUS_OPEN,
        ])->load(['employee', 'payslip', 'createdBy', 'resolvedBy', 'messages.user']);
    }

    public function employeeAddMessage(User $user, string $uuid, string $message): Dispute
    {
        $dispute = $this->findEmployeeDispute($user, $uuid);

        return $this->addMessage($dispute, $user, $message);
    }

    public function employerIndex(User $user, array $filters)
    {
        return $this->disputeRepository->paginateForMerchant(
            $this->merchantId($user),
            $filters
        );
    }

    public function employerShow(User $user, string $uuid): Dispute
    {
        return $this->findEmployerDispute($user, $uuid);
    }

    public function employerStore(User $user, array $data): Dispute
    {
        $merchantId = $this->merchantId($user);
        $employee = $this->merchantEmployee($merchantId, $data['employee_uuid'] ?? null);
        $payslip = $this->merchantPayslip($merchantId, $data['payslip_uuid'] ?? null);

        if ($payslip && $employee && $payslip->employee_id !== $employee->id) {
            throw ValidationException::withMessages([
                'payslip_uuid' => ['The payslip does not belong to the selected employee.'],
            ]);
        }

        return Dispute::create([
            'merchant_id' => $merchantId,
            'reference' => $this->newReference(),
            'employee_id' => $employee?->id,
            'payslip_id' => $payslip?->id,
            'created_by_user_id' => $user->id,
            'subject' => $data['subject'],
            'description' => $data['description'],
            'priority' => $data['priority'] ?? Dispute::PRIORITY_NORMAL,
            'status' => Dispute::STATUS_OPEN,
        ])->load(['employee', 'payslip', 'createdBy', 'resolvedBy', 'messages.user']);
    }

    public function employerUpdate(User $user, string $uuid, array $data): Dispute
    {
        $dispute = $this->findEmployerDispute($user, $uuid);
        $updateData = collect($data)->only(['status', 'priority'])->toArray();

        if (isset($updateData['status']) && in_array(
            $updateData['status'],
            [Dispute::STATUS_RESOLVED, Dispute::STATUS_CLOSED],
            true
        )) {
            $updateData['resolved_by_user_id'] = $user->id;
            $updateData['resolved_at'] = now();
        }

        if (isset($updateData['status']) && ! in_array(
            $updateData['status'],
            [Dispute::STATUS_RESOLVED, Dispute::STATUS_CLOSED],
            true
        )) {
            $updateData['resolved_by_user_id'] = null;
            $updateData['resolved_at'] = null;
        }

        $dispute->update($updateData);

        return $this->findEmployerDispute($user, $uuid);
    }

    public function employerAddMessage(User $user, string $uuid, string $message): Dispute
    {
        return $this->addMessage(
            $this->findEmployerDispute($user, $uuid),
            $user,
            $message
        );
    }

    private function addMessage(Dispute $dispute, User $user, string $message): Dispute
    {
        DB::transaction(function () use ($dispute, $user, $message) {
            $dispute->messages()->create([
                'user_id' => $user->id,
                'message' => $message,
            ]);

            if ($dispute->status === Dispute::STATUS_OPEN) {
                $dispute->update(['status' => Dispute::STATUS_PENDING]);
            }
        });

        return $this->disputeRepository->findByUuid($dispute->uuid)
            ->load(['merchant', 'employee', 'payslip', 'createdBy', 'resolvedBy', 'messages.user']);
    }

    private function newReference(): string
    {
        return 'CASE-' . now()->format('Ymd') . '-' . Str::upper(Str::random(8));
    }

    private function findEmployeeDispute(User $user, string $uuid): Dispute
    {
        $dispute = $this->disputeRepository->findForEmployeeUser($uuid, $user->id);

        if (! $dispute) {
            throw ValidationException::withMessages([
                'dispute' => ['Case not found.'],
            ]);
        }

        return $dispute;
    }

    private function findEmployerDispute(User $user, string $uuid): Dispute
    {
        $dispute = $this->disputeRepository->findForMerchant($uuid, $this->merchantId($user));

        if (! $dispute) {
            throw ValidationException::withMessages([
                'dispute' => ['Dispute not found.'],
            ]);
        }

        return $dispute;
    }

    private function employeeForUser(User $user): Employee
    {
        $employee = Employee::query()->where('user_id', $user->id)->first();

        if (! $employee) {
            throw ValidationException::withMessages([
                'employee' => ['Employee profile not found.'],
            ]);
        }

        return $employee;
    }

    private function employeePayslip(Employee $employee, ?string $uuid): ?Payslip
    {
        if (! $uuid) {
            return null;
        }

        $payslip = Payslip::query()
            ->where('uuid', $uuid)
            ->where('employee_id', $employee->id)
            ->first();

        if (! $payslip) {
            throw ValidationException::withMessages([
                'payslip_uuid' => ['Payslip not found.'],
            ]);
        }

        return $payslip;
    }

    private function merchantEmployee(int $merchantId, ?string $uuid): ?Employee
    {
        if (! $uuid) {
            return null;
        }

        $employee = Employee::query()
            ->where('merchant_id', $merchantId)
            ->where('uuid', $uuid)
            ->first();

        if (! $employee) {
            throw ValidationException::withMessages([
                'employee_uuid' => ['Employee not found for this merchant.'],
            ]);
        }

        return $employee;
    }

    private function merchantPayslip(int $merchantId, ?string $uuid): ?Payslip
    {
        if (! $uuid) {
            return null;
        }

        $payslip = Payslip::query()
            ->where('merchant_id', $merchantId)
            ->where('uuid', $uuid)
            ->first();

        if (! $payslip) {
            throw ValidationException::withMessages([
                'payslip_uuid' => ['Payslip not found for this merchant.'],
            ]);
        }

        return $payslip;
    }

    private function merchantId(User $user): int
    {
        if (! $user->merchant_id) {
            throw ValidationException::withMessages([
                'merchant' => ['Employer is not assigned to a merchant.'],
            ]);
        }

        return $user->merchant_id;
    }
}
