<?php

namespace App\Application\CommandHandlers\Rating;

use App\Application\Commands\Rating\GetRatingsCommand;
use App\Domain\Repositories\IRatingRepository;

class GetRatingsHandler
{
    public function __construct(
        private IRatingRepository $repository
    ) {
    }

    public function handle(GetRatingsCommand $command)
    {
        $queryParam = $command->queryParam;

        return $this->repository->getRatingsByQueryParams($queryParam->toArray(), [
            'user',
            'film',
        ]);
    }
}
