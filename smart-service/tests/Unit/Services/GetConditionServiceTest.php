<?php

namespace App\Tests\Unit\Services;

use App\Conditions\ConditionStrategyInterface;
use App\Exceptions\ConditionNotFoundException;
use App\Services\GetConditionService;
use App\Tests\Support\UnitTester;
use Codeception\Test\Unit;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Contracts\Service\ServiceProviderInterface;

class GetConditionServiceTest extends Unit
{
    protected UnitTester $tester;
    protected ServiceProviderInterface&MockObject $serviceProviderMock;
    protected GetConditionService $sut;

    public function testGetConditionReturnsStrategyWhenExists(): void
    {
        $conditionName = 'test_condition';

        $conditionStrategyMock = $this->createMock(ConditionStrategyInterface::class);
        $this->serviceProviderMock
            ->expects($this->once())
            ->method('has')
            ->with($this->equalTo($conditionName))
            ->willReturn(true);
        $this->serviceProviderMock
            ->expects($this->once())
            ->method('get')
            ->with($this->equalTo($conditionName))
            ->willReturn($conditionStrategyMock);

        $result = $this->sut->getCondition($conditionName);

        $this->assertSame($conditionStrategyMock, $result);
    }

    public function testGetConditionThrowsExceptionWhenNotExists(): void
    {
        $conditionName = 'nonexistent_condition';

        $this->serviceProviderMock
            ->expects($this->once())
            ->method('has')
            ->with($this->equalTo($conditionName))
            ->willReturn(false);

        $this->expectException(ConditionNotFoundException::class);
        $this->expectExceptionMessage(sprintf('Condition "%s" does not exist.', $conditionName));

        $this->sut->getCondition($conditionName);
    }

    protected function _before(): void
    {
        $this->serviceProviderMock = $this->createMock(ServiceProviderInterface::class);

        $this->sut = new GetConditionService($this->serviceProviderMock);
    }
}
