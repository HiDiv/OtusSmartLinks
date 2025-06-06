<?php

namespace App\Tests\Unit\Conditions;

use App\Conditions\BaseTimestampCondition;
use App\Conditions\ConditionStrategyInterface;
use App\Exceptions\DateTimeConditionException;
use App\Helpers\ClockInterface;
use App\Helpers\DateTimeParserInterface;
use App\Tests\Support\UnitTester;
use Codeception\Test\Unit;
use DateTimeImmutable;
use DateTimeZone;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\HttpFoundation\Request;

class BaseTimestampConditionTest extends Unit
{
    protected UnitTester $tester;
    protected DateTimeParserInterface&MockObject $parserMock;
    protected ClockInterface&MockObject $clockMock;
    protected ConditionStrategyInterface $sut;

    public function testTimestampEquals(): void
    {
        $request = Request::create('/any');
        $dateTimeStr = '2025-06-06 11:49:00';
        $utc = new DateTimeZone('UTC');

        $this->parserMock
            ->expects($this->once())
            ->method('parseUtc')
            ->with($dateTimeStr)
            ->willReturn(new DateTimeImmutable($dateTimeStr, $utc));

        $this->clockMock
            ->expects($this->once())
            ->method('nowUtc')
            ->willReturn(new DateTimeImmutable($dateTimeStr, $utc));

        $result = $this->sut->check($request, ['timestamp' => $dateTimeStr]);

        $this->assertTrue($result);
    }

    public function testTimestampNotEquals(): void
    {
        $request = Request::create('/any');
        $dateTimeStr = '2025-06-06 11:49:00';
        $utc = new DateTimeZone('UTC');

        $this->parserMock
            ->expects($this->once())
            ->method('parseUtc')
            ->with($dateTimeStr)
            ->willReturn(new DateTimeImmutable($dateTimeStr, $utc));

        $this->clockMock
            ->expects($this->once())
            ->method('nowUtc')
            ->willReturn(new DateTimeImmutable('2025-06-06 11:49:01', $utc));

        $result = $this->sut->check($request, ['timestamp' => $dateTimeStr]);

        $this->assertFalse($result);
    }

    public function testCheckThrowsExceptionWhenTimestampKeyMissing(): void
    {
        $request = Request::create('/any');

        $this->expectException(DateTimeConditionException::class);
        $this->expectExceptionMessage("Parameter 'timestamp' is missing");

        $this->sut->check($request, []);
    }

    public function testCheckThrowsExceptionWhenTimestampNotString(): void
    {
        $request = Request::create('/any');

        // Случай: timestamp не строка
        $this->expectException(DateTimeConditionException::class);
        $this->expectExceptionMessage("Parameter 'timestamp' must be not empty string");

        $this->sut->check($request, ['timestamp' => 123]);
    }

    public function testCheckThrowsExceptionWhenTimestampNotEmpty(): void
    {
        $request = Request::create('/any');

        $this->expectException(DateTimeConditionException::class);
        $this->expectExceptionMessage("Parameter 'timestamp' must be not empty string");

        $this->sut->check($request, ['timestamp' => '']);
    }

    protected function _before(): void
    {
        $this->parserMock = $this->makeEmpty(DateTimeParserInterface::class);
        $this->clockMock = $this->makeEmpty(ClockInterface::class);

        $this->sut = new class ($this->parserMock, $this->clockMock) extends BaseTimestampCondition {
            protected function compare(DateTimeImmutable $now, DateTimeImmutable $target): bool
            {
                return $now == $target;
            }
        };
    }
}
