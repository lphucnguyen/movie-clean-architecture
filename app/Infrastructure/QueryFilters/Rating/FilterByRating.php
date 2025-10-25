<?php

namespace App\Infrastructure\QueryFilters\Rating;

use Closure;

class FilterByRating
{
    public function __construct(
        public array $queryParams
    ) {
    }

    public function handle($ratings, Closure $next)
    {
        $queryParams = $this->queryParams;

        $ratings =  $ratings->where(function ($query) use ($queryParams) {
            $query->when($queryParams['searchRating'], function ($q) use ($queryParams) {
                return $q->where('rating', (array)$queryParams['searchRating']);
            });
        });

        return $next($ratings);
    }
}
