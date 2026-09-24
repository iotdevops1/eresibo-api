<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class DisputeMessage extends Model
{
    use HasUuids;

    protected $fillable = [
        'uuid',
        'dispute_id',
        'user_id',
        'message',
    ];

    public function uniqueIds(): array
    {
        return ['uuid'];
    }

    public function dispute()
    {
        return $this->belongsTo(Dispute::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
