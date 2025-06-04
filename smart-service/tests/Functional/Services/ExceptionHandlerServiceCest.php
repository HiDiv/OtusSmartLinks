<?php

namespace App\Tests\Functional\Services;

use App\Services\ExceptionHandlerInterface;
use App\Services\ExceptionHandlerService;
use App\Tests\Support\FunctionalTester;
use Exception;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ExceptionHandlerServiceCest
{
    public function exceptionHandlerInterfaceResolvesToService(FunctionalTester $I): void
    {
        $sut = $I->grabService(ExceptionHandlerInterface::class);

        $I->assertInstanceOf(ExceptionHandlerService::class, $sut);
    }

    public function testDefaultExceptionHandler(FunctionalTester $I): void
    {
        $request = new Request();
        $exception = new Exception('test exception');

        /** @var ExceptionHandlerInterface $sut */
        $sut = $I->grabService(ExceptionHandlerInterface::class);

        $result = $sut->handle($request, $exception);

        $I->assertSame(Response::HTTP_INTERNAL_SERVER_ERROR, $result->getStatusCode());
        $I->assertSame($exception->getMessage(), $result->getContent());
    }
}
