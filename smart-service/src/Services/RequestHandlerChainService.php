<?php

namespace App\Services;

use App\Exceptions\NotFoundException;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class RequestHandlerChainService implements RequestHandlerChainInterface
{
    private ?RequestHandlerInterface $chain = null;

    public function __construct(
        #[AutowireIterator('app.request_handler')]
        iterable $handlers,
    ) {
        $previous = null;

        foreach ($handlers as $handler) {
            if (!$previous) {
                $this->chain = $handler;
            } else {
                $previous->setNext($handler);
            }

            $previous = $handler;
        }
    }

    public function handle(Request $request): Response
    {
        if ($this->chain === null) {
            throw new NotFoundException('No handlers configured');
        }

        return $this->chain->handle($request);
    }
}
