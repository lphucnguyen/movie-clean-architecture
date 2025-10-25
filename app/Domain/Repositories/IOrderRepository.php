<?php

namespace App\Domain\Repositories;

use App\Shared\Domain\Repositories\IRepository;

interface IOrderRepository extends IRepository
{
    public function getUnpaidOrder($limit);

    public function getOrdersByQueryParams(array $queryParams, array $relations = []);
}
