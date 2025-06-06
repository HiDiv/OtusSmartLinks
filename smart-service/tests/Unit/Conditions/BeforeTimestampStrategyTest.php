<?php

namespace App\Tests\Unit\Conditions;

use App\Conditions\BeforeTimestampStrategy;
use App\Helpers\ClockInterface;
use App\Helpers\DateTimeParserInterface;
use App\Tests\Support\UnitTester;
use Codeception\Test\Unit;
use DateTimeImmutable;
use DateTimeZone;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\HttpFoundation\Request;

class BeforeTimestampStrategyTest extends Unit
{
    protected UnitTester $tester;
    protected DateTimeParserInterface&MockObject $parserMock;
    protected ClockInterface&MockObject $clockMock;
    protected BeforeTimestampStrategy $sut;

    public function testCompareBefore(): void
    {
        $request = Request::create('/any');

        $result = $this->sut->check($request, ['timestamp' => '2025-06-06 11:49:02']);

        $this->assertTrue($result);
    }

    public function testCompareEqual(): void
    {
        $request = Request::create('/any');

        $result = $this->sut->check($request, ['timestamp' => '2025-06-06 11:49:01']);

        $this->assertFalse($result);
    }

    public function testCompareAfter(): void
    {
        $request = Request::create('/any');

        $result = $this->sut->check($request, ['timestamp' => '2025-06-06 11:49:00']);

        $this->assertFalse($result);
    }

    protected function _before(): void
    {
        $utc = new DateTimeZone('UTC');

        $this->parserMock = $this->makeEmpty(DateTimeParserInterface::class);
        $this->parserMock
            ->expects($this->once())
            ->method('parseUtc')
            ->willReturnCallback(function ($dateStr) use ($utc) {
                return new DateTimeImmutable($dateStr, $utc);
            });

        $this->clockMock = $this->makeEmpty(ClockInterface::class);
        $this->clockMock
            ->expects($this->once())
            ->method('nowUtc')
            ->willReturn(new DateTimeImmutable('2025-06-06 11:49:01', $utc));

        $this->sut = new BeforeTimestampStrategy($this->parserMock, $this->clockMock);
    }
}
