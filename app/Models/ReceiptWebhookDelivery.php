<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class ReceiptWebhookDelivery extends Model
{
    use HasUuids;

    public const STATUS_PENDING = 1;
    public const STATUS_SENT = 2;
    public const STATUS_FAILED = 3;

    protected $fillable = [
        'uuid',
        'receipt_id',
        'endpoint',
        'status',
        'attempts',
        'last_http_status',
        'last_response',
        'last_attempted_at',
        'delivered_at',
        'next_attempt_at',
    ];

    protected $casts = [
        'status' => 'integer',
        'attempts' => 'integer',
        'last_http_status' => 'integer',
        'last_attempted_at' => 'datetime',
        'delivered_at' => 'datetime',
        'next_attempt_at' => 'datetime',
    ];

    public function uniqueIds(): array
    {
        return ['uuid'];
    }

    /*
    |--------------------------------------------------------------------------
    | Receipt
    |--------------------------------------------------------------------------
    */

    public function receipt()
    {
        return $this->belongsTo(
            Receipt::class,
            'receipt_id'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Status helpers
    |--------------------------------------------------------------------------
    */

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isSent(): bool
    {
        return $this->status === self::STATUS_SENT;
    }

    public function isFailed(): bool
    {
        return $this->status === self::STATUS_FAILED;
    }
}