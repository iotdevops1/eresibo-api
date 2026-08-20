<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PayrollBatch extends Model
{
    use HasUuids, SoftDeletes;

    protected $fillable = [
        'uuid',
        'employer_id',
        'batch_no',
        'pay_period_start',
        'pay_period_end',
        'pay_date',
        'description',
        'total_employees',
        'total_gross_amount',
        'total_deduction_amount',
        'total_net_amount',
        'status',
        'submitted_at',
        'completed_at',
    ];

    protected $casts = [
        'pay_period_start' => 'date',
        'pay_period_end' => 'date',
        'pay_date' => 'date',
        'submitted_at' => 'datetime',
        'completed_at' => 'datetime',
        'total_employees' => 'integer',
        'total_gross_amount' => 'decimal:2',
        'total_deduction_amount' => 'decimal:2',
        'total_net_amount' => 'decimal:2',
        'status' => 'integer',
    ];

    public const STATUS_DRAFT = 1;
    public const STATUS_PROCESSING = 2;
    public const STATUS_SUBMITTED = 3;
    public const STATUS_PARTIALLY_PROCESSED = 4;
    public const STATUS_COMPLETED = 5;
    public const STATUS_FAILED = 6;
    public const STATUS_CANCELLED = 7;

    public function uniqueIds(): array
    {
        return ['uuid'];
    }

    public function employer()
    {
        return $this->belongsTo(
            User::class,
            'employer_id'
        );
    }

    public function items()
    {
        return $this->hasMany(
            PayrollBatchItem::class,
            'payroll_batch_id'
        );
    }
}