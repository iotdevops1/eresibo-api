<?php

namespace App\Filters;

use Illuminate\Database\Eloquent\Builder;

abstract class BaseFilter
{
    protected Builder $query;

    protected array $filters = [];

    /**
     * List of supported filters.
     */
    protected array $allowedFilters = [];

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    /**
     * Apply all allowed filters.
     */
    public function apply(Builder $query): Builder
    {
        $this->query = $query;
        foreach ($this->allowedFilters as $filter) {
            $value = $this->filters[$filter] ?? null;
            if (!$this->shouldSkip($value)) {
                $this->{$filter}($value);
            }
        }

        $this->sort();

        return $this->query;
    }

    /**
     * Determine whether a filter should be skipped.
     */
    protected function shouldSkip(mixed $value): bool
    {
        return $value === null || $value === '';
    }

    /**
     * Default sorting.
     */
    protected function sort(): void
    {
        $sortBy = $this->filters['sort_by'] ?? 'created_at';
        $sortOrder = $this->filters['sort_order'] ?? 'desc';

        $this->query->orderBy($sortBy, $sortOrder);
    }

    /**
     * Default pagination.
     */
    public function perPage(): int
    {
        return $this->filters['per_page'] ?? 20;
    }
}