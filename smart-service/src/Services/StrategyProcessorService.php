<?php

namespace App\Services;

use App\Exceptions\ActionNotFoundException;
use App\Exceptions\ConditionNotFoundException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class StrategyProcessorService implements StrategyProcessorInterface
{
    public function __construct(
        private readonly StrategyFetcherInterface $strategyFetcher,
        private readonly ConditionEvaluatorInterface $conditionEvaluator,
        private readonly ActionExecutorInterface $actionExecutor,
    ) {
    }

    /**
     * @throws ConditionNotFoundException
     * @throws ActionNotFoundException
     */
    public function process(Request $request): ?Response
    {
        $path = $request->getPathInfo();
        $strategies = $this->strategyFetcher->fetchForPath($path);
        if (empty($strategies)) {
            return null;
        }

        foreach ($strategies as $strategy) {
            if ($this->conditionEvaluator->allConditionsMet($strategy, $request)) {
                return $this->actionExecutor->execute($strategy, $request);
            }
        }

        return null;
    }
}
