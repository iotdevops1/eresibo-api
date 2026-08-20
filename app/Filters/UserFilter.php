<?php

namespace App\Filters;

class UserFilter extends BaseFilter
{
    protected array $allowedFilters = [
        'search',
        'status',
        'role_code',
    ];

    protected function search(string $value): void
    {
        $this->query->where(function ($query) use ($value) {
            $query->where('name', 'like', "%{$value}%")
            ->orWhere('email', 'like', "%{$value}%")
            ->orWhere('mobile', 'like', "%{$value}%");
        });
    }

    protected function status(string $value): void
    {
        $this->query->where('status', $value);
    }

    protected function role_code(string $value): void
    {
        $this->query->whereHas('role', function ($query) use ($value) {
            $query->where('code', $value);
        });
    }
}