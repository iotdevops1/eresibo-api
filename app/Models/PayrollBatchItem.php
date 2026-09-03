<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class PayrollBatchItem extends Model
{
    use HasUuids;

    protected $fillable = [
        'uuid',
        'payroll_batch_id',
        'employee_id',
        'gross_amount',
        'deduction_amount',
        'net_amount',
        'status',
        'payslip_id',
        'payout_status',
        'payout_reference',
    ];

    protected $casts = [
        'gross_amount' => 'decimal:2',
        'deduction_amount' => 'decimal:2',
        'net_amount' => 'decimal:2',
        'status' => 'integer',
    ];

    public const STATUS_PENDING = 1;
    public const STATUS_PROCESSING = 2;
    public const STATUS_COMPLETED = 3;
    public const STATUS_FAILED = 4;
    public const STATUS_CANCELLED = 5;

    public function uniqueIds(): array
    {
        return ['uuid'];
    }

    public function payrollBatch()
    {
        return $this->belongsTo(
            PayrollBatch::class,
            'payroll_batch_id'
        );
    }

    public function employee()
    {
        return $this->belongsTo(
            Employee::class,
            'employee_id'
        );
    }
}