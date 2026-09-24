<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Dispute extends Model
{
    use HasUuids;

    public const STATUS_OPEN = 1;
    public const STATUS_PENDING = 2;
    public const STATUS_RESOLVED = 3;
    public const STATUS_CLOSED = 4;

    public const PRIORITY_LOW = 1;
    public const PRIORITY_NORMAL = 2;
    public const PRIORITY_HIGH = 3;

    protected $fillable = [
        'uuid',
        'reference',
        'merchant_id',
        'employee_id',
        'payslip_id',
        'created_by_user_id',
        'subject',
        'description',
        'status',
        'priority',
        'resolved_by_user_id',
        'resolved_at',
    ];

    protected $casts = [
        'status' => 'integer',
        'priority' => 'integer',
        'resolved_at' => 'datetime',
    ];

    public function uniqueIds(): array
    {
        return ['uuid'];
    }

    public function merchant()
    {
        return $this->belongsTo(Merchant::class);
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function payslip()
    {
        return $this->belongsTo(Payslip::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function resolvedBy()
    {
        return $this->belongsTo(User::class, 'resolved_by_user_id');
    }

    public function messages()
    {
        return $this->hasMany(DisputeMessage::class)->orderBy('created_at');
    }
}
