<?php

namespace App\Tests\Unit\Services;

use App\Actions\ActionStrategyInterface;
use App\Entity\Action;
use App\Entity\Strategy;
use App\Services\ActionExecutorService;
use App\Services\GetActionInterface;
use App\Tests\Support\UnitTester;
use Codeception\Test\Unit;
use PHPUnit\Framework\MockObject\MockObject;
use RuntimeException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Uid\Uuid;

class ActionExecutorServiceTest extends Unit
{
    protected UnitTester $tester;
    protected GetActionInterface&MockObject $actionResolverMock;
    protected ActionExecutorService $sut;

    public function testExecuteThrowsExceptionWhenNoActionDefined(): void
    {
        $request = Request::create('/any');

        $strategyMock = $this->createMock(Strategy::class);
        $strategyMock
            ->expects($this->once())
            ->method('getAction')
            ->willReturn(null);
        $strategyMock
            ->expects($this->once())
            ->method('getId')
            ->willReturn(Uuid::fromString('79a1f5dd-3cd1-46c8-a8ae-5cbb30e69269'));
        $strategyMock
            ->expects($this->once())
            ->method('getPath')
            ->willReturn('/test-path');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(
            "No Action defined for strategy id '79a1f5dd-3cd1-46c8-a8ae-5cbb30e69269' and path '/test-path'."
        );

        $this->sut->execute($strategyMock, $request);
    }

    public function testExecuteInvokesActionHandlerAndReturnsResponse(): void
    {
        $validUrl = 'https://example.com';
        $request = Request::create('/any');
        $handlerTag = 'some_tag';

        $actionEntryMock = $this->createMock(Action::class);
        $actionEntryMock
            ->expects($this->once())
            ->method('getHandlerTag')
            ->willReturn($handlerTag);
        $actionEntryMock
            ->expects($this->once())
            ->method('getParameters')
            ->willReturn(['url' => $validUrl]);

        $strategyEntryMock = $this->createMock(Strategy::class);
        $strategyEntryMock
            ->expects($this->once())
            ->method('getAction')
            ->willReturn($actionEntryMock);
        $strategyEntryMock
            ->expects($this->never())
            ->method('getId');
        $strategyEntryMock
            ->expects($this->never())
            ->method('getPath');

        $responseStub = new Response('OK', Response::HTTP_OK);
        $actionStrategyMock = $this->createMock(ActionStrategyInterface::class);
        $actionStrategyMock
            ->expects($this->once())
            ->method('handle')
            ->with(
                $this->isInstanceOf(Request::class),
                $this->equalTo(['url' => $validUrl])
            )
            ->willReturn($responseStub);

        $this->actionResolverMock
            ->expects($this->once())
            ->method('getAction')
            ->with($handlerTag)
            ->willReturn($actionStrategyMock);

        $result = $this->sut->execute($strategyEntryMock, $request);

        $this->assertSame($responseStub, $result);
    }

    protected function _before(): void
    {
        $this->actionResolverMock = $this->makeEmpty(GetActionInterface::class);

        $this->sut = new ActionExecutorService($this->actionResolverMock);
    }
}
