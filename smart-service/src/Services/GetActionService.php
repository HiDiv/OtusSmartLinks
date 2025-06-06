<?php

namespace App\Services;

use App\Actions\ActionStrategyInterface;
use App\Exceptions\ActionNotFoundException;
use Symfony\Component\DependencyInjection\Attribute\AutowireLocator;
use Symfony\Contracts\Service\ServiceProviderInterface;

class GetActionService implements GetActionInterface
{
    public function __construct(
        #[AutowireLocator('app.action')]
        private readonly ServiceProviderInterface $serviceProvider,
    ) {
    }

    public function getAction(string $actionName): ActionStrategyInterface
    {
        if (!$this->serviceProvider->has($actionName)) {
            throw new ActionNotFoundException(sprintf('Action "%s" does not exist.', $actionName));
        }

        return $this->serviceProvider->get($actionName);
    }
}
