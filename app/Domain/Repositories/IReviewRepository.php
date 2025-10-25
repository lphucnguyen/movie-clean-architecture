<?php

namespace App\Domain\Repositories;

use App\Shared\Domain\Repositories\IRepository;

interface IReviewRepository extends IRepository
{
    public function getReviewByQueryParams(array $queryParams, array $withRelations = []);
}
