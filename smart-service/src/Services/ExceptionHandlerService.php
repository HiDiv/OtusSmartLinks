<?php

namespace App\Services;

use Symfony\Component\DependencyInjection\Attribute\AutowireLocator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Service\ServiceProviderInterface;
use Throwable;

class ExceptionHandlerService implements ExceptionHandlerInterface
{
    public function __construct(
        #[AutowireLocator('app.exception_handler')]
        private readonly ServiceProviderInterface $serviceProvider,
    ) {
    }

    /**
     * @throws Throwable
     */
    public function handle(Request $request, Throwable $exception): Response
    {
        $exceptionClass = get_class($exception);
        if ($this->serviceProvider->has($exceptionClass)) {
            return $this->serviceProvider->get($exceptionClass)->handle($request, $exception);
        }

        if ($this->serviceProvider->has('')) {
            return $this->serviceProvider->get('')->handle($request, $exception);
        }

        throw $exception;
    }
}
