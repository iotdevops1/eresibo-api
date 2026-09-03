<?php

namespace App\Repositories\Merchant;

use App\Models\User;

class EmployerRepository
{
    public function paginateByMerchant(int $merchantId,array $filters = []) {
        $query = User::query()
            ->with('role')
            ->where('merchant_id', $merchantId)
            ->whereHas('role', function ($q) {
                $q->where('code', 'EMPLOYER');
            });

        if (!empty($filters['search'])) {
            $search = $filters['search'];

            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('mobile', 'like', "%{$search}%");
            });
        }

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query
            ->orderBy('name')
            ->paginate($filters['per_page'] ?? 20);
    }

    public function findByUuid(string $uuid,int $merchantId): ?User {
        return User::query()
            ->with('role')
            ->where('uuid', $uuid)
            ->where('merchant_id', $merchantId)
            ->whereHas('role', function ($q) {
                $q->where('code', 'EMPLOYER');
            })
            ->first();
    }
}