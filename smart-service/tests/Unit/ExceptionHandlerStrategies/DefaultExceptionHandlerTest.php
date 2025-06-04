<?php

namespace App\Tests\Unit\ExceptionHandlerStrategies;

use App\ExceptionHandlerStrategies\DefaultExceptionHandler;
use App\Tests\Support\UnitTester;
use Codeception\Test\Unit;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

class DefaultExceptionHandlerTest extends Unit
{
    protected UnitTester $tester;
    protected DefaultExceptionHandler $handler;
    protected Request $request;

    public function testHandleHttpExceptionReturnsSameStatusHeadersAndMessage(): void
    {
        $message = 'Forbidden access';
        $statusCode = 403;
        $headers = ['X-Custom' => 'Value123'];
        $exception = new HttpException($statusCode, $message, null, $headers);

        $result = $this->handler->handle($this->request, $exception);

        $this->assertEquals($statusCode, $result->getStatusCode());
        $this->assertEquals($message, $result->getContent());
        $this->assertTrue($result->headers->has('X-Custom'));
        $this->assertEquals('Value123', $result->headers->get('X-Custom'));
    }

    public function testHandleGenericExceptionReturns500WithExceptionMessage(): void
    {
        $message = 'Something went wrong';
        $exception = new \RuntimeException($message);

        $result = $this->handler->handle($this->request, $exception);

        $this->assertEquals(Response::HTTP_INTERNAL_SERVER_ERROR, $result->getStatusCode());
        $this->assertEquals($message, $result->getContent());
    }

    protected function _before(): void
    {
        $this->handler = new DefaultExceptionHandler();
        $this->request = new Request();
    }
}
