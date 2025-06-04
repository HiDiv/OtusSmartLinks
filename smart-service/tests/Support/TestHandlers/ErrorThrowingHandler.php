<?php

namespace App\Tests\Support\TestHandlers;

use App\Services\BaseRequestHandler;
use RuntimeException;
use Symfony\Component\DependencyInjection\Attribute\AsTaggedItem;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

#[AsTaggedItem('app.request_handler', priority: 1000)]
class ErrorThrowingHandler extends BaseRequestHandler
{
    public function handle(Request $request): Response
    {
        throw new RuntimeException('Test exception from ErrorThrowingHandler');
    }
}
