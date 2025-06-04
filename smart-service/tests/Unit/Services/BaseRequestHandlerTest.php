<?php

namespace App\Tests\Unit\Services;

use App\Exceptions\NotFoundException;
use App\Services\BaseRequestHandler;
use App\Services\RequestHandlerInterface;
use App\Tests\Support\UnitTester;
use Codeception\Test\Unit;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class BaseRequestHandlerTest extends Unit
{
    protected UnitTester $tester;

    public function testHandleWithoutNextThrowsNotFoundException(): void
    {
        $sut = $this->getFirstHandler();
        $request = new Request();

        $this->expectException(NotFoundException::class);
        $this->expectExceptionMessage('Not found next handler');

        $sut->handle($request);
    }

    private function getFirstHandler(): BaseRequestHandler
    {
        return new class () extends BaseRequestHandler {
            public function handle(Request $request): Response
            {
                return $this->next($request);
            }
        };
    }

    public function testHandleDelegatesToNextHandler(): void
    {
        $firstHandler = $this->getFirstHandler();

        $expectedResponse = new Response('OK', Response::HTTP_OK);

        $secondHandler = $this->createMock(RequestHandlerInterface::class);
        $secondHandler
            ->expects($this->once())
            ->method('handle')
            ->with($this->isInstanceOf(Request::class))
            ->willReturn($expectedResponse);

        $firstHandler->setNext($secondHandler);

        $request = new Request();
        $actualResponse = $firstHandler->handle($request);

        $this->assertSame($expectedResponse, $actualResponse);
    }
}
