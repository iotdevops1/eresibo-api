<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class UserPreference extends Model
{
    use HasUuids;

    public const RECEIPT_MODE_ALWAYS = 'ALWAYS';
    public const RECEIPT_MODE_ASK = 'ASK';
    public const RECEIPT_MODE_NEVER = 'NEVER';

    public const RECEIPT_TYPE_SIMPLE_PROOF = 'SIMPLE_PROOF';
    public const RECEIPT_TYPE_ERECEIPT = 'ERECEIPT';
    public const RECEIPT_TYPE_OFFICIAL_ERESIBO = 'OFFICIAL_ERESIBO';

    protected $fillable = [
        'uuid',
        'user_id',
        'receipt_generation_mode',
        'default_receipt_type',
        'locale',
    ];

    public function uniqueIds(): array
    {
        return ['uuid'];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
