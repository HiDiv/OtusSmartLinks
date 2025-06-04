<?php

namespace App\ExceptionHandlerStrategies;

use App\Services\ExceptionHandlerStrategyInterface;
use Symfony\Component\DependencyInjection\Attribute\AsTaggedItem;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Throwable;

#[AsTaggedItem('')]
class DefaultExceptionHandler implements ExceptionHandlerStrategyInterface
{
    public function handle(Request $request, Throwable $exception): Response
    {
        if ($exception instanceof HttpExceptionInterface) {
            return new Response($exception->getMessage(), $exception->getStatusCode(), $exception->getHeaders());
        }

        return new Response($exception->getMessage(), 500);
    }
}
