<?php

namespace App\Services;

use App\Entity\Strategy;
use App\Exceptions\ActionNotFoundException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

interface ActionExecutorInterface
{
    /**
     * @throws ActionNotFoundException
     */
    public function execute(Strategy $strategy, Request $request): Response;
}
