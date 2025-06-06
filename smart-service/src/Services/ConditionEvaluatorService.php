<?php

namespace App\Services;

use App\Entity\Strategy;
use Symfony\Component\HttpFoundation\Request;

class ConditionEvaluatorService implements ConditionEvaluatorInterface
{
    public function __construct(
        private readonly GetConditionInterface $conditionResolver,
    ) {
    }

    public function allConditionsMet(Strategy $strategy, Request $request): bool
    {
        $conditions = $strategy->getConditions();
        if ($conditions->isEmpty()) {
            return true;
        }

        foreach ($conditions as $conditionEntity) {
            $tag = $conditionEntity->getHandlerTag();
            $params = $conditionEntity->getParameters() ?? [];

            $checker = $this->conditionResolver->getCondition($tag);
            if (!$checker->check($request, $params)) {
                return false;
            }
        }

        return true;
    }
}
