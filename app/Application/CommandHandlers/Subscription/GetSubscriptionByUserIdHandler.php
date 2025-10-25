<?php

namespace App\Application\CommandHandlers\Subscription;

use App\Application\Commands\Subscription\GetSubscriptionByUserIdCommand;
use App\Domain\Repositories\ISubscriptionRepository;

class GetSubscriptionByUserIdHandler
{
    public function __construct(
        public ISubscriptionRepository $repository
    ) {
    }

    public function handle(GetSubscriptionByUserIdCommand $command)
    {
        return $this->repository->getSubscriptionByUserId($command->userId, [
            'plan:id,slug'
        ]);
    }
}
