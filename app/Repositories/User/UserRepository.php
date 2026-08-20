<?php

namespace App\Repositories\User;

use App\Models\User;
use App\Filters\UserFilter;
use App\Repositories\BaseRepository;

class UserRepository extends BaseRepository
{
    public function __construct(
        User $model
    ) {
        $this->model = $model;
    }

    public function paginate(array $filters)
    {
        $query = $this->model
            ->newQuery()
            ->withTrashed()
            ->with('role');

        $filter = new UserFilter($filters);

        return $filter
            ->apply($query)
            ->paginate(
                $filter->perPage()
            );
    }

    public function findByUuid(string $uuid): ?User
    {
        return $this->model
            ->newQuery()
            ->withTrashed()
            ->with('role')
            ->where('uuid', $uuid)
            ->first();
    }
}