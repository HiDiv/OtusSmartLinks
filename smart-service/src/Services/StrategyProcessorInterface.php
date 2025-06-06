<?php

namespace App\Services;

use App\Exceptions\ActionNotFoundException;
use App\Exceptions\ConditionNotFoundException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

interface StrategyProcessorInterface
{
    /**
     * @throws ConditionNotFoundException
     * @throws ActionNotFoundException
     */
    public function process(Request $request): ?Response;
}
