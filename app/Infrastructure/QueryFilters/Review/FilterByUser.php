<?php

namespace App\Infrastructure\QueryFilters\Review;

use Closure;

class FilterByUser
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
                return $q->whereHas('user', function ($q2) use ($queryParams) {
                    $q2->where('username', 'LIKE', "%" . $queryParams['searchKey'] . "%");
                });
            });

            $query->when($queryParams['client'], function ($q) use ($queryParams) {
                return $q->whereHas('user', function ($q2) use ($queryParams) {
                    return $q2->where('id', $queryParams['client']);
                });
            });
        });

        return $next($reviews);
    }
}
