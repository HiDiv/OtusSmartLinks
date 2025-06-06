<?php

namespace App\Actions;

use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

#[AutoconfigureTag('app.action')]
interface ActionStrategyInterface
{
    public function handle(Request $request, array $params): Response;
}
