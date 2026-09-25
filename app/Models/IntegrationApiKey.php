<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class IntegrationApiKey extends Model
{
    use HasUuids;

    public const SCOPES = ['receipts.create', 'receipts.read', 'funding.confirm'];

    protected $fillable = [
        'uuid',
        'name',
        'key_hash',
        'environment',
        'scopes',
        'active',
        'expires_at',
        'last_used_at',
    ];

    protected $casts = [
        'scopes' => 'array',
        'active' => 'boolean',
        'expires_at' => 'datetime',
        'last_used_at' => 'datetime',
    ];

    public function uniqueIds(): array
    {
        return ['uuid'];
    }

    public function isValid(): bool
    {
        return $this->active &&
            (
                ! $this->expires_at ||
                $this->expires_at->isFuture()
            );
    }

    public function hasScope(string $scope): bool
    {
        return in_array($scope, $this->scopes ?? [], true);
    }
}
