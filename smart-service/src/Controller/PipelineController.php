<?php

namespace App\Controller;

use App\Services\ExceptionHandlerInterface;
use App\Services\RequestHandlerChainInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Throwable;

class PipelineController extends AbstractController
{
    public function __construct(
        private readonly RequestHandlerChainInterface $handlerChain,
        private readonly ExceptionHandlerInterface $exceptionHandler,
    ) {
    }

    #[Route(
        path: '/{url}',
        requirements: ['url' => '.+'],
        methods: ['GET','POST','PUT','DELETE','PATCH']
    )]
    public function process(Request $request): Response
    {
        try {
            return $this->handlerChain->handle($request);
        } catch (Throwable $exception) {
            return $this->exceptionHandler->handle($request, $exception);
        }
    }
}
