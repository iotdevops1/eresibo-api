<?php

namespace App\Repositories\Merchant;

use App\Models\Merchant;
use App\Repositories\BaseRepository;

class MerchantRepository extends BaseRepository
{
    public function __construct(Merchant $model)
    {
        $this->model = $model;
    }

    public function paginate(array $filters = [])
    {
        $query = $this->model
            ->newQuery()
            ->withCount([
                'employers',
                'employees',
            ]);
            

        if (!empty($filters['search'])) {
            $search = $filters['search'];

            $query->where(function ($q) use ($search) {
                $q->where('merchant_code', 'like', "%{$search}%")
                    ->orWhere('business_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query
            ->orderBy('business_name')
            ->paginate($filters['per_page'] ?? 20);
    }

    public function findByUuid(string $uuid): ?Merchant
    {
        return $this->model
            ->newQuery()
            ->withCount([
                'employers',
                'employees',
            ])
            ->where('uuid', $uuid)
            ->first();
    }
}