<?php

namespace App\Infrastructure\Repositories;

use App\Domain\Enums\Order\OrderStatus;
use App\Domain\Models\Order;
use App\Domain\Repositories\IOrderRepository;
use App\Shared\Infrastructure\Repositories\BaseRepository;
use Illuminate\Pipeline\Pipeline;

class OrderRepository extends BaseRepository implements IOrderRepository
{
    public function __construct(
        Order $model
    ) {
        parent::__construct($model);
    }

    public function getUnpaidOrder($limit)
    {
        return $this->model->whereNull('paid_at')->where('status', OrderStatus::COMPLETED->value)->limit($limit)->get();
    }

    public function getOrdersByQueryParams(array $queryParams, array $relations = [])
    {
        $query = $this->makeQuery();

        return app(Pipeline::class)
            ->send($query)
            ->through([
                new \App\Infrastructure\QueryFilters\Order\FilterByPaymentName($queryParams),
                new \App\Infrastructure\QueryFilters\Order\FilterByUserName($queryParams),
                new \App\Infrastructure\QueryFilters\Order\FilterByPlan($queryParams),
            ])
            ->thenReturn()
            ->with($relations)
            ->latest()
            ->paginate(config('app.perPage'));
    }
}