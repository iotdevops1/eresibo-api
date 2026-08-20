<?php

namespace App\Services\Payroll;

use App\Models\Employee;
use App\Models\PayrollBatch;
use App\Repositories\Payroll\PayrollBatchRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PayrollBatchService
{
    public function __construct(
        protected PayrollBatchRepository $payrollBatchRepository
    ) {
    }

    public function index(int $employerId, array $filters) {
        return $this->payrollBatchRepository
            ->paginateByEmployer(
                $employerId,
                $filters
            );
    }

    public function show(string $uuid, int $employerId): PayrollBatch {
        $batch = $this->payrollBatchRepository
            ->findByUuidForEmployer(
                $uuid,
                $employerId
            );

        if (! $batch) {
            throw ValidationException::withMessages([
                'payroll_batch' => [
                    'Payroll batch not found.'
                ],
            ]);
        }

        return $batch;
    }

    public function store(int $employerId, array $data): PayrollBatch {
        return DB::transaction(function () use ($employerId, $data) {
            /*
            |--------------------------------------------------------------------------
            | Validate employees belong to employer
            |--------------------------------------------------------------------------
            */

            $employeeIds = collect(
                $data['items']
            )->pluck('employee_id')
                ->unique()
                ->values();

            $employees = Employee::query()
                ->where('employer_id', $employerId)
                ->whereIn('id', $employeeIds)
                ->where('status', Employee::STATUS_ACTIVE)
                ->get()
                ->keyBy('id');

            if ($employees->count() !== $employeeIds->count()) {
                throw ValidationException::withMessages([
                    'items' => [
                        'One or more employees do not belong to this employer or are not active.'
                    ],
                ]);
            }

            /*
            |--------------------------------------------------------------------------
            | Create Batch
            |--------------------------------------------------------------------------
            */

            $batch = $this->payrollBatchRepository->create([
                'employer_id' => $employerId,
                'batch_no' => $data['batch_no'],
                'pay_period_start' => $data['pay_period_start'],
                'pay_period_end' => $data['pay_period_end'],
                'pay_date' => $data['pay_date'],
                'description' => $data['description'] ?? null,
                'total_employees' => 0,
                'total_gross_amount' => 0,
                'total_deduction_amount' => 0,
                'total_net_amount' => 0,
                'status' => PayrollBatch::STATUS_DRAFT,
            ]);

            /*
            |--------------------------------------------------------------------------
            | Create Batch Items
            |--------------------------------------------------------------------------
            */

            $totalGross = 0;
            $totalDeductions = 0;
            $totalNet = 0;

            foreach ($data['items'] as $item) {

                $gross = (float) $item['gross_amount'];
                $deduction = (float) $item['deduction_amount'];
                $net = $gross - $deduction;

                if ($net < 0) {
                    throw ValidationException::withMessages([
                        'items' => [
                            'Deduction amount cannot exceed gross amount.'
                        ],
                    ]);
                }

                $batch->items()->create([
                    'employee_id' => $item['employee_id'],
                    'gross_amount' => $gross,
                    'deduction_amount' => $deduction,
                    'net_amount' => $net,
                    'status' => 1,
                    'payout_status' => 'NOT_RELEASED',
                ]);

                $totalGross += $gross;
                $totalDeductions += $deduction;
                $totalNet += $net;
            }

            /*
            |--------------------------------------------------------------------------
            | Update Batch Summary
            |--------------------------------------------------------------------------
            */

            $batch->update([
                'total_employees' => count($data['items']),
                'total_gross_amount' => $totalGross,
                'total_deduction_amount' => $totalDeductions,
                'total_net_amount' => $totalNet,
            ]);

            return $batch->load([
                'items.employee'
            ]);
        });
    }

    public function update(PayrollBatch $batch, array $data): PayrollBatch {
        if ($batch->status !== PayrollBatch::STATUS_DRAFT) {
            throw ValidationException::withMessages([
                'payroll_batch' => [
                    'Only DRAFT payroll batches can be updated.'
                ],
            ]);
        }

        return DB::transaction(function () use ($batch, $data) {
            /*
            |--------------------------------------------------------------------------
            | Update Batch Header
            |--------------------------------------------------------------------------
            */

            $batchData = collect($data)
                ->only([
                    'batch_no',
                    'pay_period_start',
                    'pay_period_end',
                    'pay_date',
                    'description',
                ])
                ->toArray();

            if (!empty($batchData)) {
                $this->payrollBatchRepository->update(
                    $batch,
                    $batchData
                );
            }

            /*
            |--------------------------------------------------------------------------
            | Update Items
            |--------------------------------------------------------------------------
            */

            if (array_key_exists('items', $data)) {

                $employeeIds = collect($data['items'])
                    ->pluck('employee_id')
                    ->unique()
                    ->values();

                $employees = Employee::query()
                    ->where('employer_id', $batch->employer_id)
                    ->whereIn('id', $employeeIds)
                    ->where('status', Employee::STATUS_ACTIVE)
                    ->get()
                    ->keyBy('id');

                if ($employees->count() !== $employeeIds->count()) {
                    throw ValidationException::withMessages([
                        'items' => [
                            'One or more employees do not belong to this employer or are not active.'
                        ],
                    ]);
                }

                /*
                |--------------------------------------------------------------------------
                | Replace Draft Items
                |--------------------------------------------------------------------------
                */

                $batch->items()->delete();

                $totalGross = 0;
                $totalDeductions = 0;
                $totalNet = 0;

                foreach ($data['items'] as $item) {

                    $gross = (float) $item['gross_amount'];
                    $deduction = (float) $item['deduction_amount'];
                    $net = $gross - $deduction;

                    if ($net < 0) {
                        throw ValidationException::withMessages([
                            'items' => [
                                'Deduction amount cannot exceed gross amount.'
                            ],
                        ]);
                    }

                    $batch->items()->create([
                        'employee_id' => $item['employee_id'],
                        'gross_amount' => $gross,
                        'deduction_amount' => $deduction,
                        'net_amount' => $net,
                        'status' => 1,
                        'payout_status' => 'NOT_RELEASED',
                    ]);

                    $totalGross += $gross;
                    $totalDeductions += $deduction;
                    $totalNet += $net;
                }

                $batch->update([
                    'total_employees' => count($data['items']),
                    'total_gross_amount' => $totalGross,
                    'total_deduction_amount' => $totalDeductions,
                    'total_net_amount' => $totalNet,
                ]);
            }

            return $batch->load('items.employee');
        });
    }

    public function submit(PayrollBatch $batch): PayrollBatch {
        if ($batch->status !== PayrollBatch::STATUS_DRAFT) {
            throw ValidationException::withMessages([
                'payroll_batch' => [
                    'Only DRAFT payroll batches can be submitted.'
                ],
            ]);
        }

        if ($batch->items()->count() === 0) {
            throw ValidationException::withMessages([
                'payroll_batch' => [
                    'Payroll batch must contain at least one employee.'
                ],
            ]);
        }

        $batch->update([
            'status' => PayrollBatch::STATUS_SUBMITTED,
            'submitted_at' => now(),
        ]);

        return $batch->refresh()->load('items.employee');
    }
}