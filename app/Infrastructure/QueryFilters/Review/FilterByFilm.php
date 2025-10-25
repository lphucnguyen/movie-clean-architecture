<?php

namespace App\Infrastructure\QueryFilters\Review;

use Closure;

class FilterByFilm
{
    public function __construct(
        public array $queryParams
    ) {
    }

    public function handle($reviews, Closure $next)
    {
        $queryParams = $this->queryParams;

        $reviews =  $reviews->where(function ($query) use ($queryParams) {
            $query->when($queryParams['searchKey'], function ($q) use ($queryParams) {
                $searchKey = $queryParams['searchKey'];

                return $q->whereHas('film', function ($q2) use ($searchKey) {
                    $q2->where('name', 'LIKE', "%{$searchKey}%");
                });
            });
        });

        return $next($reviews);
    }
}
