<?php

namespace App\Services;

use App\Actions\ActionStrategyInterface;
use App\Exceptions\ActionNotFoundException;

interface GetActionInterface
{
    /**
     * @throws ActionNotFoundException
     */
    public function getAction(string $actionName): ActionStrategyInterface;
}
