<?php

namespace App\Application\CommandHandlers\Subscription;

use App\Application\Commands\Subscription\GetSubscriptionsCommand;
use App\Domain\Repositories\ISubscriptionRepository;

class GetSubscriptionsHandler
{
    public function __construct(
        public ISubscriptionRepository $repository
    ) {
    }

    public function handle(GetSubscriptionsCommand $command)
    {
        return $this->repository->getSubscriptionsByQueryParams([
            'searchKey' => $command->searchKey,
            [
                'user:id,username,last_name,first_name',
                'plan:id,slug'
            ]
        ]);
    }
}
