<?php

namespace App\Tests\Unit\Services;

use App\Actions\ActionStrategyInterface;
use App\Exceptions\ActionNotFoundException;
use App\Services\GetActionService;
use App\Tests\Support\UnitTester;
use Codeception\Test\Unit;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Contracts\Service\ServiceProviderInterface;

class GetActionServiceTest extends Unit
{
    protected UnitTester $tester;
    protected ServiceProviderInterface&MockObject $serviceProviderMock;
    protected GetActionService $sut;

    public function testGetActionReturnsStrategyWhenExists(): void
    {
        $actionName = 'test_action';

        $actionStrategyMock = $this->createMock(ActionStrategyInterface::class);
        $this->serviceProviderMock
            ->expects($this->once())
            ->method('has')
            ->with($this->equalTo($actionName))
            ->willReturn(true);
        $this->serviceProviderMock
            ->expects($this->once())
            ->method('get')
            ->with($this->equalTo($actionName))
            ->willReturn($actionStrategyMock);

        $result = $this->sut->getAction($actionName);

        $this->assertSame($actionStrategyMock, $result);
    }

    public function testGetActionThrowsExceptionWhenNotExists(): void
    {
        $actionName = 'nonexistent_action';

        $this->serviceProviderMock
            ->expects($this->once())
            ->method('has')
            ->with($this->equalTo($actionName))
            ->willReturn(false);

        $this->expectException(ActionNotFoundException::class);
        $this->expectExceptionMessage(sprintf('Action "%s" does not exist.', $actionName));

        $this->sut->getAction($actionName);
    }

    protected function _before(): void
    {
        $this->serviceProviderMock = $this->createMock(ServiceProviderInterface::class);

        $this->sut = new GetActionService($this->serviceProviderMock);
    }
}
