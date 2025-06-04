<?php

namespace App\Services;

use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

#[AutoconfigureTag('app.exception_handler')]
interface ExceptionHandlerStrategyInterface
{
    public function handle(Request $request, Throwable $exception): Response;
}
