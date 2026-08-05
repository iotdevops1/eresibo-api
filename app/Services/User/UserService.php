<?php

namespace App\Services\User;

use App\Filters\UserFilter;
use App\Models\User;

class UserService
{
    public function index(array $filters)
    {
        $query = User::query()
            ->with('role');

        $filter = new UserFilter($filters);

        return $filter
            ->apply($query)
            ->paginate(
                $filter->perPage()
            );
    }

    public function store(array $data)
    {
    }

    public function show(string $uuid)
    {
    }

    public function update(string $uuid, array $data)
    {
    }

    public function destroy(string $uuid)
    {
    }
}