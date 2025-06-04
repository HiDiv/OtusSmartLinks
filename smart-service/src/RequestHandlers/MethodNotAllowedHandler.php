<?php

namespace App\RequestHandlers;

use App\Services\BaseRequestHandler;
use Symfony\Component\DependencyInjection\Attribute\AsTaggedItem;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

#[AsTaggedItem('app.request_handler', priority: 900)]
class MethodNotAllowedHandler extends BaseRequestHandler
{
    public function handle(Request $request): Response
    {
        if ($request->getMethod() === Request::METHOD_GET) {
            return $this->next($request);
        }

        return new Response(
            'Method Not Allowed. Only GET is supported.',
            Response::HTTP_METHOD_NOT_ALLOWED,
            [
                'Allow' => Request::METHOD_GET,
            ]
        );
    }
}
