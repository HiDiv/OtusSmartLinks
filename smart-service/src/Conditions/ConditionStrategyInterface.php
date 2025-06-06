<?php

namespace App\Conditions;

use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Symfony\Component\HttpFoundation\Request;

#[AutoconfigureTag('app.condition')]
interface ConditionStrategyInterface
{
    public function check(Request $request, array $params): bool;
}
