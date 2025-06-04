<?php

namespace App\Tests\Unit\RequestHandlers;

use App\Exceptions\NotFoundException;
use App\RequestHandlers\MethodNotAllowedHandler;
use App\Services\RequestHandlerInterface;
use App\Tests\Support\UnitTester;
use Codeception\Test\Unit;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class MethodNotAllowedHandlerTest extends Unit
{
    protected UnitTester $tester;
    protected MethodNotAllowedHandler $sut;

    public function testGetWithoutNextThrowsNotFoundException(): void
    {
        $request = Request::create('/any', Request::METHOD_GET);

        $this->expectException(NotFoundException::class);
        $this->expectExceptionMessage('Not found next handler');

        $this->sut->handle($request);
    }

    public function testGetWithNextDelegatesToNextHandler(): void
    {
        $request = Request::create('/some-path', Request::METHOD_GET);

        $nextResponse = new Response('OK from next', Response::HTTP_OK);

        $nextHandler = $this->createMock(RequestHandlerInterface::class);
        $nextHandler
            ->expects($this->once())
            ->method('handle')
            ->with($this->callback(function (Request $request) {
                return $request->getMethod() === Request::METHOD_GET;
            }))
            ->willReturn($nextResponse);

        $this->sut->setNext($nextHandler);

        $result = $this->sut->handle($request);

        $this->assertSame($nextResponse, $result);
    }

    public function testNonGetReturnsMethodNotAllowedResponse(): void
    {
        $request = Request::create('/another-path', Request::METHOD_POST);

        $result = $this->sut->handle($request);

        $this->assertEquals(Response::HTTP_METHOD_NOT_ALLOWED, $result->getStatusCode());
        $this->assertEquals('Method Not Allowed. Only GET is supported.', $result->getContent());
        $this->assertTrue($result->headers->has('Allow'));
        $this->assertEquals(Request::METHOD_GET, $result->headers->get('Allow'));
    }

    protected function _before(): void
    {
        $this->sut = new MethodNotAllowedHandler();
    }
}
