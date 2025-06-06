<?php

namespace App\Services;

use App\Entity\Strategy;
use App\Exceptions\ConditionNotFoundException;
use Symfony\Component\HttpFoundation\Request;

interface ConditionEvaluatorInterface
{
    /**
     * @throws ConditionNotFoundException
     */
    public function allConditionsMet(Strategy $strategy, Request $request): bool;
}
