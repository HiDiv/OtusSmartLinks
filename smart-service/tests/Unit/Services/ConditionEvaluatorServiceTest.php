<?php

namespace App\Tests\Unit\Services;

use App\Conditions\ConditionStrategyInterface;
use App\Entity\Condition;
use App\Entity\Strategy;
use App\Exceptions\ConditionNotFoundException;
use App\Services\ConditionEvaluatorService;
use App\Services\GetConditionInterface;
use App\Tests\Support\UnitTester;
use Codeception\Test\Unit;
use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\HttpFoundation\Request;

class ConditionEvaluatorServiceTest extends Unit
{
    protected UnitTester $tester;
    protected GetConditionInterface&MockObject $conditionResolverMock;
    protected ConditionEvaluatorService $sut;

    public function testAllConditionsMetReturnsTrueWhenNoConditions(): void
    {
        $strategyMock = $this->createMock(Strategy::class);
        $strategyMock
            ->expects($this->once())
            ->method('getConditions')
            ->willReturn(new ArrayCollection());

        $request = Request::create('/any');

        $result = $this->sut->allConditionsMet($strategyMock, $request);

        $this->assertTrue($result);
    }

    public function testAllConditionsMetReturnsTrueWhenSingleConditionTrue(): void
    {
        $conditionEntityMock = $this->createMock(Condition::class);
        $conditionEntityMock
            ->expects($this->once())
            ->method('getHandlerTag')
            ->willReturn('tag1');
        $conditionEntityMock
            ->expects($this->once())
            ->method('getParameters')
            ->willReturn(['param' => 'value']);

        $collection = new ArrayCollection([$conditionEntityMock]);

        $strategyMock = $this->createMock(Strategy::class);
        $strategyMock
            ->expects($this->once())
            ->method('getConditions')
            ->willReturn($collection);

        $checkerMock = $this->createMock(ConditionStrategyInterface::class);
        $checkerMock
            ->expects($this->once())
            ->method('check')
            ->with(
                $this->isInstanceOf(Request::class),
                $this->equalTo(['param' => 'value'])
            )
            ->willReturn(true);

        $this->conditionResolverMock
            ->expects($this->once())
            ->method('getCondition')
            ->with('tag1')
            ->willReturn($checkerMock);

        $request = Request::create('/any');

        $result = $this->sut->allConditionsMet($strategyMock, $request);

        $this->assertTrue($result);
    }

    public function testAllConditionsMetReturnsFalseWhenSingleConditionFalse(): void
    {
        $conditionEntityMock = $this->createMock(Condition::class);
        $conditionEntityMock
            ->method('getHandlerTag')
            ->willReturn('tag1');
        $conditionEntityMock
            ->method('getParameters')
            ->willReturn(['param' => 'value']);

        $collection = new ArrayCollection([$conditionEntityMock]);

        $strategyMock = $this->createMock(Strategy::class);
        $strategyMock
            ->expects($this->once())
            ->method('getConditions')
            ->willReturn($collection);

        $checkerMock = $this->createMock(ConditionStrategyInterface::class);
        $checkerMock
            ->expects($this->once())
            ->method('check')
            ->with(
                $this->isInstanceOf(Request::class),
                $this->equalTo(['param' => 'value'])
            )
            ->willReturn(false);

        $this->conditionResolverMock
            ->expects($this->once())
            ->method('getCondition')
            ->with('tag1')
            ->willReturn($checkerMock);

        $request = Request::create('/any');

        $result = $this->sut->allConditionsMet($strategyMock, $request);

        $this->assertFalse($result);
    }

    public function testAllConditionsMetStopsOnFirstFalseCondition(): void
    {
        $condition1 = $this->createMock(Condition::class);
        $condition1
            ->method('getHandlerTag')
            ->willReturn('tag1');
        $condition1
            ->method('getParameters')
            ->willReturn([]);

        $condition2 = $this->createMock(Condition::class);
        $condition2
            ->method('getHandlerTag')
            ->willReturn('tag2');
        $condition2
            ->method('getParameters')
            ->willReturn([]);

        $collection = new ArrayCollection([$condition1, $condition2]);

        $strategyMock = $this->createMock(Strategy::class);
        $strategyMock
            ->expects($this->once())
            ->method('getConditions')
            ->willReturn($collection);

        $checker1 = $this->createMock(ConditionStrategyInterface::class);
        $checker1
            ->expects($this->once())
            ->method('check')
            ->willReturn(false);

        $checker2 = $this->createMock(ConditionStrategyInterface::class);
        $checker2
            ->expects($this->never())
            ->method('check');

        $this->conditionResolverMock
            ->expects($this->once())
            ->method('getCondition')
            ->willReturnMap([
                ['tag1', $checker1],
                ['tag2', $checker1],
            ]);

        $request = Request::create('/any');

        $result = $this->sut->allConditionsMet($strategyMock, $request);

        $this->assertFalse($result);
    }

    public function testAllConditionsMetThrowsExceptionWhenConditionNotFound(): void
    {
        $conditionEntityMock = $this->createMock(Condition::class);
        $conditionEntityMock
            ->method('getHandlerTag')
            ->willReturn('unknown_tag');
        $conditionEntityMock
            ->method('getParameters')
            ->willReturn([]);

        $collection = new ArrayCollection([$conditionEntityMock]);

        $strategyMock = $this->createMock(Strategy::class);
        $strategyMock
            ->expects($this->once())
            ->method('getConditions')
            ->willReturn($collection);

        $this->conditionResolverMock
            ->expects($this->once())
            ->method('getCondition')
            ->with('unknown_tag')
            ->willThrowException(new ConditionNotFoundException());

        $request = Request::create('/any');

        $this->expectException(ConditionNotFoundException::class);

        $this->sut->allConditionsMet($strategyMock, $request);
    }

    protected function _before(): void
    {
        $this->conditionResolverMock = $this->createMock(GetConditionInterface::class);

        $this->sut = new ConditionEvaluatorService($this->conditionResolverMock);
    }
}
