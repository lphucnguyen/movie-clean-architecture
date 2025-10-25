<?php

namespace App\Domain\Repositories;

use App\Shared\Domain\Repositories\IRepository;

interface ISubscriptionRepository extends IRepository
{
    public function getSubscriptionsByQueryParams(array $queryParams, array $withRelations = []);

    public function getSubscriptionByUserId(string $userId, array $withRelations = []);
}
