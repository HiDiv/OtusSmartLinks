<?php

namespace App\Actions;

use Symfony\Component\DependencyInjection\Attribute\AsTaggedItem;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

#[AsTaggedItem('gone')]
class ResourceIsGoneStrategy implements ActionStrategyInterface
{
    public function handle(Request $request, array $params): Response
    {
        return new Response('The resource is no longer available.', Response::HTTP_GONE);
    }
}
