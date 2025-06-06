<?php

namespace App\Tests\Unit\Services;

use App\Entity\Strategy;
use App\Exceptions\ActionNotFoundException;
use App\Exceptions\ConditionNotFoundException;
use App\Services\ActionExecutorInterface;
use App\Services\ConditionEvaluatorInterface;
use App\Services\StrategyFetcherInterface;
use App\Services\StrategyProcessorService;
use App\Tests\Support\UnitTester;
use Codeception\Test\Unit;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class StrategyProcessorServiceTest extends Unit
{
    protected UnitTester $tester;
    protected StrategyFetcherInterface&MockObject $strategyFetcherMock;
    protected ConditionEvaluatorInterface&MockObject $conditionEvaluatorMock;
    protected ActionExecutorInterface&MockObject $actionExecutorMock;
    protected StrategyProcessorService $sut;

    public function testProcessReturnsNullWhenNoStrategies(): void
    {
        $path = '/test';
        $request = Request::create($path);

        $this->strategyFetcherMock
            ->expects($this->once())
            ->method('fetchForPath')
            ->with($path)
            ->willReturn([]);

        $result = $this->sut->process($request);

        $this->assertNull($result);
    }

    public function testProcessExecutesActionOnFirstMatchingStrategy(): void
    {
        $path = '/test';
        $request = Request::create($path);

        $strategy1 = $this->createMock(Strategy::class);
        $strategy2 = $this->createMock(Strategy::class);
        $this->strategyFetcherMock
            ->expects($this->once())
            ->method('fetchForPath')
            ->with($path)
            ->willReturn([$strategy1, $strategy2]);

        $this->conditionEvaluatorMock
            ->expects($this->once())
            ->method('allConditionsMet')
            ->with($strategy1, $request)
            ->willReturn(true);

        $responseStub = new Response('OK', Response::HTTP_OK);
        $this->actionExecutorMock
            ->expects($this->once())
            ->method('execute')
            ->with($strategy1, $request)
            ->willReturn($responseStub);

        $result = $this->sut->process($request);

        $this->assertSame($responseStub, $result);
    }

    public function testProcessExecutesActionOnSecondMatchingStrategy(): void
    {
        $path = '/test';
        $request = Request::create($path);

        $strategy1 = $this->createMock(Strategy::class);
        $strategy2 = $this->createMock(Strategy::class);
        $this->strategyFetcherMock
            ->expects($this->once())
            ->method('fetchForPath')
            ->with($path)
            ->willReturn([$strategy1, $strategy2]);

        $this->conditionEvaluatorMock
            ->expects($this->exactly(2))
            ->method('allConditionsMet')
            ->willReturnMap([
                [$strategy1, $request, false],
                [$strategy2, $request, true],
            ]);

        $responseStub = new Response('OK2', Response::HTTP_CREATED);
        $this->actionExecutorMock
            ->expects($this->once())
            ->method('execute')
            ->with($strategy2, $request)
            ->willReturn($responseStub);

        $result = $this->sut->process($request);

        $this->assertSame($responseStub, $result);
    }

    public function testProcessReturnsNullWhenNoStrategyMatches(): void
    {
        $path = '/test';
        $request = Request::create($path);

        $strategy1 = $this->createMock(Strategy::class);
        $strategy2 = $this->createMock(Strategy::class);
        $this->strategyFetcherMock
            ->expects($this->once())
            ->method('fetchForPath')
            ->with($path)
            ->willReturn([$strategy1, $strategy2]);

        $this->conditionEvaluatorMock
            ->expects($this->exactly(2))
            ->method('allConditionsMet')
            ->willReturn(false);

        $this->actionExecutorMock
            ->expects($this->never())
            ->method('execute');

        $result = $this->sut->process($request);

        $this->assertNull($result);
    }

    public function testProcessPropagatesConditionNotFoundException(): void
    {
        $path = '/test';
        $request = Request::create($path);

        $strategy = $this->createMock(Strategy::class);
        $this->strategyFetcherMock
            ->expects($this->once())
            ->method('fetchForPath')
            ->with($path)
            ->willReturn([$strategy]);

        $this->conditionEvaluatorMock
            ->expects($this->once())
            ->method('allConditionsMet')
            ->with($strategy, $request)
            ->willThrowException(new ConditionNotFoundException('Condition missing'));

        $this->expectException(ConditionNotFoundException::class);
        $this->expectExceptionMessage('Condition missing');

        $this->sut->process($request);
    }

    public function testProcessPropagatesActionNotFoundException(): void
    {
        $path = '/test';
        $request = Request::create($path);

        $strategy = $this->createMock(Strategy::class);
        $this->strategyFetcherMock
            ->expects($this->once())
            ->method('fetchForPath')
            ->with($path)
            ->willReturn([$strategy]);

        $this->conditionEvaluatorMock
            ->expects($this->once())
            ->method('allConditionsMet')
            ->with($strategy, $request)
            ->willReturn(true);

        $this->actionExecutorMock
            ->expects($this->once())
            ->method('execute')
            ->with($strategy, $request)
            ->willThrowException(new ActionNotFoundException('Action missing'));

        $this->expectException(ActionNotFoundException::class);
        $this->expectExceptionMessage('Action missing');

        $this->sut->process($request);
    }

    protected function _before(): void
    {
        $this->strategyFetcherMock = $this->createMock(StrategyFetcherInterface::class);
        $this->conditionEvaluatorMock = $this->createMock(ConditionEvaluatorInterface::class);
        $this->actionExecutorMock = $this->createMock(ActionExecutorInterface::class);

        $this->sut = new StrategyProcessorService(
            $this->strategyFetcherMock,
            $this->conditionEvaluatorMock,
            $this->actionExecutorMock
        );
    }
}
