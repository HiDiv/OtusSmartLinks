<?php

namespace App\RequestHandlers;

use App\Services\BaseRequestHandler;
use App\Services\StrategyProcessorInterface;
use Symfony\Component\DependencyInjection\Attribute\AsTaggedItem;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

#[AsTaggedItem('app.request_handler', priority: 500)]
class SmartRedirectHandler extends BaseRequestHandler
{
    public function __construct(
        private readonly StrategyProcessorInterface $strategyProcessor
    ) {
    }

    public function handle(Request $request): Response
    {
        $response = $this->strategyProcessor->process($request);

        return $response ?? $this->next($request);
    }
}
