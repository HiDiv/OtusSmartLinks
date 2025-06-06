<?php

namespace App\Services;

use App\Conditions\ConditionStrategyInterface;
use App\Exceptions\ConditionNotFoundException;

interface GetConditionInterface
{
    /**
     * @throws ConditionNotFoundException
     */
    public function getCondition(string $conditionName): ConditionStrategyInterface;
}
