<?php

namespace App\Application\CommandHandlers\Order;

use App\Application\Commands\Order\GetOrdersCommand;
use App\Domain\Repositories\IOrderRepository;

class GetOrdersHandler
{
    public function __construct(
        public IOrderRepository $repository
    ) {
    }

    public function handle(GetOrdersCommand $command)
    {
        $withRelations = ['user:id,username,last_name,first_name'];
        return $this->repository->getOrdersByQueryParams($command->queryParam->toArray(), $withRelations);
    }
}
