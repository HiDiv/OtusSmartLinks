<?php

namespace App\Services;

use App\Conditions\ConditionStrategyInterface;
use App\Exceptions\ConditionNotFoundException;
use Symfony\Component\DependencyInjection\Attribute\AutowireLocator;
use Symfony\Contracts\Service\ServiceProviderInterface;

class GetConditionService implements GetConditionInterface
{
    public function __construct(
        #[AutowireLocator('app.condition')]
        private readonly ServiceProviderInterface $serviceProvider,
    ) {
    }

    public function getCondition(string $conditionName): ConditionStrategyInterface
    {
        if (!$this->serviceProvider->has($conditionName)) {
            throw new ConditionNotFoundException(sprintf('Condition "%s" does not exist.', $conditionName));
        }

        return $this->serviceProvider->get($conditionName);
    }
}
