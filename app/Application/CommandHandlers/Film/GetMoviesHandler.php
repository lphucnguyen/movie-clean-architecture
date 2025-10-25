<?php

namespace App\Application\CommandHandlers\Film;

use App\Application\Commands\Film\GetMoviesCommand;
use App\Domain\Repositories\IFilmRepository;

class GetMoviesHandler
{
    public function __construct(
        private IFilmRepository $repository
    ) {
    }

    public function handle(GetMoviesCommand $command)
    {
        $queryParam = $command->queryParam;

        return $this->repository->getFilmsByQueryParams(
            $queryParam->toArray(),
            [
                'categories',
                'ratings',
            ]
        );
    }
}
