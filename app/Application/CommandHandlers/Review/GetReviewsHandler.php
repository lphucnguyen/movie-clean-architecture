<?php

namespace App\Application\CommandHandlers\Review;

use App\Application\Commands\Review\GetReviewsCommand;
use App\Domain\Repositories\IReviewRepository;

class GetReviewsHandler
{
    public function __construct(
        public IReviewRepository $repository
    ) {
    }

    public function handle(GetReviewsCommand $command)
    {
        return $this->repository->getReviewByQueryParams([
            'searchKey' => $command->searchKey,
            'client' => $command->client
        ], [
            'user',
            'film'
        ]);
    }
}
