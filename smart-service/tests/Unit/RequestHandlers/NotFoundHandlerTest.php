<?php

namespace App\Tests\Unit\RequestHandlers;

use App\RequestHandlers\NotFoundHandler;
use App\Tests\Support\UnitTester;
use Codeception\Test\Unit;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class NotFoundHandlerTest extends Unit
{
    protected UnitTester $tester;
    protected NotFoundHandler $sut;

    public function testHandleAlwaysReturns404Response(): void
    {
        $request = new Request();

        $response = $this->sut->handle($request);

        $this->assertEquals(Response::HTTP_NOT_FOUND, $response->getStatusCode());
        $this->assertEquals('Resource not found', $response->getContent());
    }

    protected function _before(): void
    {
        $this->sut = new NotFoundHandler();
    }
}
