<?php

namespace App\Tests\Unit\Helpers;

use App\Helpers\SystemClock;
use App\Tests\Support\UnitTester;
use Codeception\Test\Unit;
use DateTimeImmutable;
use DateTimeZone;

class SystemClockTest extends Unit
{
    protected UnitTester $tester;

    public function testNowUtcReturnsDateTimeImmutableWithUtcTimezone(): void
    {
        $sut = new SystemClock();

        $result = $sut->nowUtc();

        $this->assertEquals('UTC', $result->getTimezone()->getName());
    }

    public function testNowUtcIsApproximatelyCurrentUtcTime(): void
    {
        $sut = new SystemClock();

        $before = new DateTimeImmutable('now', new DateTimeZone('UTC'));
        $returned = $sut->nowUtc();
        $after = new DateTimeImmutable('now', new DateTimeZone('UTC'));

        $returnedTs = $returned->getTimestamp();
        $beforeTs = $before->getTimestamp();
        $afterTs = $after->getTimestamp();

        $this->assertGreaterThanOrEqual($beforeTs - 1, $returnedTs);
        $this->assertLessThanOrEqual($afterTs + 1, $returnedTs);
    }
}
