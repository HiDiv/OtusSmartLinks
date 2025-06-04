<?php

namespace App\Services;

use App\Exceptions\NotFoundException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

abstract class BaseRequestHandler implements RequestHandlerInterface
{
    protected ?RequestHandlerInterface $nextHandler = null;

    public function setNext(RequestHandlerInterface $next): void
    {
        $this->nextHandler = $next;
    }

    protected function next(Request $request): Response
    {
        if (null === $this->nextHandler) {
            throw new NotFoundException('Not found next handler');
        }

        return $this->nextHandler->handle($request);
    }
}
