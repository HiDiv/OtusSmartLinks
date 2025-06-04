<?php

namespace App\Tests\Unit\Services;

use App\Services\ExceptionHandlerInterface;
use App\Services\ExceptionHandlerService;
use App\Tests\Support\UnitTester;
use Codeception\Test\Unit;
use InvalidArgumentException;
use RuntimeException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Contracts\Service\ServiceProviderInterface;

class ExceptionHandlerServiceTest extends Unit
{
    protected UnitTester $tester;
    protected Request $request;

    public function testHandleUsesSpecificExceptionHandler(): void
    {
        $exception = new HttpException(418, 'I’m a teapot');

        $expectedResponse = new Response('Handled by specific', 418);
        $specificHandler = $this->createMock(ExceptionHandlerInterface::class);
        $specificHandler
            ->expects($this->once())
            ->method('handle')
            ->with($this->request, $exception)
            ->willReturn($expectedResponse);

        $serviceProvider = $this->createMock(ServiceProviderInterface::class);
        $exceptionClass = get_class($exception);

        $serviceProvider
            ->expects($this->atLeastOnce())
            ->method('has')
            ->willReturnMap([
                [$exceptionClass, true],
                ['', false],
            ]);

        $serviceProvider
            ->expects($this->once())
            ->method('get')
            ->with($exceptionClass)
            ->willReturn($specificHandler);

        $sut = new ExceptionHandlerService($serviceProvider);

        $result = $sut->handle($this->request, $exception);

        $this->assertSame($expectedResponse, $result);
    }

    public function testHandleUsesDefaultHandlerWhenSpecificNotFound(): void
    {
        $exception = new RuntimeException('Something broke');

        $defaultResponse = new Response('Default handled', 500);
        $defaultHandler = $this->createMock(ExceptionHandlerInterface::class);
        $defaultHandler
            ->expects($this->once())
            ->method('handle')
            ->with($this->request, $exception)
            ->willReturn($defaultResponse);

        $serviceProvider = $this->createMock(ServiceProviderInterface::class);
        $exceptionClass = get_class($exception);

        $serviceProvider
            ->expects($this->exactly(2))
            ->method('has')
            ->willReturnMap([
                [$exceptionClass, false],
                ['', true],
            ]);

        $serviceProvider
            ->expects($this->once())
            ->method('get')
            ->with('')
            ->willReturn($defaultHandler);

        $sut = new ExceptionHandlerService($serviceProvider);

        $result = $sut->handle($this->request, $exception);

        $this->assertSame($defaultResponse, $result);
    }

    public function testHandleThrowsOriginalExceptionWhenNoHandlerFound(): void
    {
        $exception = new InvalidArgumentException('Bad argument');
        $serviceProvider = $this->createMock(ServiceProviderInterface::class);
        $exceptionClass = get_class($exception);

        $serviceProvider
            ->expects($this->exactly(2))
            ->method('has')
            ->willReturnMap([
                [$exceptionClass, false],
                ['', false],
            ]);

        $serviceProvider
            ->expects($this->never())
            ->method('get');

        $sut = new ExceptionHandlerService($serviceProvider);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Bad argument');

        $sut->handle($this->request, $exception);
    }

    protected function _before(): void
    {
        $this->request = new Request();
    }
}
