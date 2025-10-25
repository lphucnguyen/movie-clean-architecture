<?php

namespace App\Domain\Repositories;

use App\Shared\Domain\Repositories\IRepository;

interface IRatingRepository extends IRepository
{
    public function getRatingsByQueryParams(array $queryParams, array $withRelations = []);
}
