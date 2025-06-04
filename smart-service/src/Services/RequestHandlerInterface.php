<?php

namespace App\Services;

use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

#[AutoconfigureTag('app.request_handler')]
interface RequestHandlerInterface
{
    public function handle(Request $request): Response;

    public function setNext(RequestHandlerInterface $next): void;
}
