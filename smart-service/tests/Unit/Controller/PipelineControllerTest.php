<?php

namespace App\Tests\Unit\Controller;

use App\Controller\PipelineController;
use App\Services\ExceptionHandlerInterface;
use App\Services\RequestHandlerChainInterface;
use App\Tests\Support\UnitTester;
use Codeception\Test\Unit;
use PHPUnit\Framework\MockObject\MockObject;
use RuntimeException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class PipelineControllerTest extends Unit
{
    protected UnitTester $tester;
    /** @var MockObject&RequestHandlerChainInterface $handlerChain */
    protected RequestHandlerChainInterface $handlerChain;
    /** @var MockObject&ExceptionHandlerInterface $exceptionHandler */
    protected ExceptionHandlerInterface $exceptionHandler;
    protected PipelineController $sut;
    protected Request $request;

    public function testProcessReturnsResponseFromHandlerChainWhenNoException(): void
    {
        $expectedResponse = new Response('OK', Response::HTTP_OK);

        $this->handlerChain
            ->expects($this->once())
            ->method('handle')
            ->with($this->request)
            ->willReturn($expectedResponse);

        $this->exceptionHandler
            ->expects($this->never())
            ->method('handle');

        $result = $this->sut->process($this->request);

        $this->assertSame($expectedResponse, $result);
    }

    public function testProcessCatchesExceptionAndUsesExceptionHandler(): void
    {
        $exception = new RuntimeException('Something bad');
        $this->handlerChain
            ->expects($this->once())
            ->method('handle')
            ->with($this->request)
            ->willThrowException($exception);

        $fallbackResponse = new Response('Handled error', Response::HTTP_INTERNAL_SERVER_ERROR);
        $this->exceptionHandler
            ->expects($this->once())
            ->method('handle')
            ->with($this->request, $exception)
            ->willReturn($fallbackResponse);

        $result = $this->sut->process($this->request);

        $this->assertSame($fallbackResponse, $result);
    }

    protected function _before(): void
    {
        $this->handlerChain = $this->createMock(RequestHandlerChainInterface::class);
        $this->exceptionHandler = $this->createMock(ExceptionHandlerInterface::class);

        $this->sut = new PipelineController($this->handlerChain, $this->exceptionHandler);

        $this->request = new Request();
    }
}
