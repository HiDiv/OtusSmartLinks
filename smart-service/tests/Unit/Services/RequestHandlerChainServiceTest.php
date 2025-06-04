<?php

namespace App\Tests\Unit\Services;

use App\Exceptions\NotFoundException;
use App\Services\RequestHandlerChainService;
use App\Services\RequestHandlerInterface;
use App\Tests\Support\UnitTester;
use Codeception\Test\Unit;
use RuntimeException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class RequestHandlerChainServiceTest extends Unit
{
    protected UnitTester $tester;

    public function testHandleWithoutAnyHandlersThrowsNotFoundException(): void
    {
        $sut = new RequestHandlerChainService([]);

        $this->expectException(NotFoundException::class);
        $this->expectExceptionMessage('No handlers configured');

        $sut->handle(new Request());
    }

    public function testHandleWithSingleHandlerReturnsItsResponse(): void
    {
        $request = new Request();

        $singleResponse = new Response('single OK', Response::HTTP_OK);

        $singleHandler = new class ($singleResponse) implements RequestHandlerInterface {
            private Response $response;

            public function __construct(Response $response)
            {
                $this->response = $response;
            }

            public function setNext(RequestHandlerInterface $next): void
            {
            }

            public function handle(Request $request): Response
            {
                return $this->response;
            }
        };

        $sut = new RequestHandlerChainService([$singleHandler]);

        $result = $sut->handle($request);

        $this->assertSame($singleResponse, $result);
    }

    public function testHandleWithTwoHandlersFirstDelegatesToSecond(): void
    {
        $request = new Request();
        $expectedResponse = new Response('second OK', Response::HTTP_ACCEPTED);

        $secondHandler = new class ($expectedResponse) implements RequestHandlerInterface {
            private Response $response;

            public function __construct(Response $response)
            {
                $this->response = $response;
            }

            public function setNext(RequestHandlerInterface $next): void
            {
            }

            public function handle(Request $request): Response
            {
                return $this->response;
            }
        };

        $firstHandler = new class () implements RequestHandlerInterface {
            private ?RequestHandlerInterface $nextHandler = null;

            public function setNext(RequestHandlerInterface $next): void
            {
                $this->nextHandler = $next;
            }

            public function handle(Request $request): Response
            {
                if ($this->nextHandler !== null) {
                    return $this->nextHandler->handle($request);
                }
                throw new RuntimeException('Next handler not set');
            }
        };

        $sut = new RequestHandlerChainService([$firstHandler,$secondHandler]);

        $result = $sut->handle($request);

        $this->assertSame($expectedResponse, $result);
    }
}
