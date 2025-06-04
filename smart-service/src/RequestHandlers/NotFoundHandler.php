<?php

namespace App\RequestHandlers;

use App\Services\BaseRequestHandler;
use Symfony\Component\DependencyInjection\Attribute\AsTaggedItem;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

#[AsTaggedItem('NotFound', priority: 0)]
class NotFoundHandler extends BaseRequestHandler
{
    public function handle(Request $request): Response
    {
        return new Response('Resource not found', Response::HTTP_NOT_FOUND);
    }
}
