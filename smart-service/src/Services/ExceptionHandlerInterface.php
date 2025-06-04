<?php

namespace App\Services;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

interface ExceptionHandlerInterface
{
    public function handle(Request $request, Throwable $exception): Response;
}
