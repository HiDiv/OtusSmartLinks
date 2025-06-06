<?php

namespace App\Tests\Unit\RequestHandlers;

use App\RequestHandlers\SmartRedirectHandler;
use App\Services\RequestHandlerInterface;
use App\Services\StrategyProcessorInterface;
use App\Tests\Support\UnitTester;
use Codeception\Test\Unit;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class SmartRedirectHandlerTest extends Unit
{
    protected UnitTester $tester;
    protected StrategyProcessorInterface&MockObject $strategyProcessorMock;

    public function testHandleReturnsStrategyResponseWhenNotNull(): void
    {
        $expectedResponse = new Response('Strategy Response', Response::HTTP_ACCEPTED);
        $this->strategyProcessorMock
            ->expects($this->once())
            ->method('process')
            ->willReturn($expectedResponse);

        $nextHandler = $this->createMock(RequestHandlerInterface::class);
        $nextHandler->expects($this->never())->method('handle');

        $request = Request::create('/test-path');

        $sut = new SmartRedirectHandler($this->strategyProcessorMock);
        $sut->setNext($nextHandler);

        $result = $sut->handle($request);

        $this->assertSame($expectedResponse, $result);
    }

    public function testHandleCallsNextWhenStrategyReturnsNull(): void
    {
        $request = Request::create('/test-path');

        $this->strategyProcessorMock
            ->expects($this->once())
            ->method('process')
            ->willReturn(null);

        $nextResponse = new Response('Next Response', Response::HTTP_OK);
        $nextHandler = $this->createMock(RequestHandlerInterface::class);
        $nextHandler
            ->expects($this->once())
            ->method('handle')
            ->with($request)
            ->willReturn($nextResponse);

        $sut = new SmartRedirectHandler($this->strategyProcessorMock);
        $sut->setNext($nextHandler);

        $result = $sut->handle($request);

        $this->assertSame($nextResponse, $result);
    }

    protected function _before(): void
    {
        $this->strategyProcessorMock = $this->createMock(StrategyProcessorInterface::class);
    }
}
