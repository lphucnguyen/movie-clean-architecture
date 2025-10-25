<?php

namespace App\Infrastructure\QueryFilters\Order;

use Closure;

class FilterByUserName
{
    public function __construct(
        public array $queryParams
    ) {
    }

    public function handle($orders, Closure $next)
    {
        $queryParams = $this->queryParams;

        $orders =  $orders->where(function ($query) use ($queryParams) {
            $query->when($queryParams['searchKey'], function ($q) use ($queryParams) {
                return $q->whereHas('user', function ($q2) use ($queryParams) {
                    return $q2->where('username', 'like', '%' . $queryParams['searchKey'] . '%');
                });
            });
        });

        return $next($orders);
    }
}
