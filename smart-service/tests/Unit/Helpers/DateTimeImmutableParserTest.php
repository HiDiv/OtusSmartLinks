<?php

namespace App\Tests\Unit\Helpers;

use App\Exceptions\DateTimeConditionException;
use App\Helpers\DateTimeImmutableParser;
use App\Helpers\DateTimeParserInterface;
use App\Tests\Support\UnitTester;
use Codeception\Test\Unit;

class DateTimeImmutableParserTest extends Unit
{
    protected UnitTester $tester;
    protected DateTimeImmutableParser $sut;

    public function testParseUtcReturnsDateTimeImmutableWithUtcTimezone(): void
    {
        $input = '2025-12-31 15:45:00';

        $result = $this->sut->parseUtc($input);

        $this->assertEquals('2025-12-31 15:45:00', $result->format('Y-m-d H:i:s'));
        $this->assertEquals('UTC', $result->getTimezone()->getName());
    }

    public function testParseUtcIso8601UtcFormat(): void
    {
        $input = '2025-05-01T08:30:00Z';
        $result = $this->sut->parseUtc($input);

        $this->assertEquals('2025-05-01 08:30:00', $result->format('Y-m-d H:i:s'));
        $this->assertEquals('UTC', $result->getTimezone()->getName());
    }

    public function testParseUtcIso8601WithOffset(): void
    {
        $input = '2025-01-01T12:00:00+02:00';

        $result = $this->sut->parseUtc($input);

        $this->assertEquals('2025-01-01 10:00:00', $result->format('Y-m-d H:i:s'));
        $this->assertEquals('UTC', $result->getTimezone()->getName());
    }

    public function testParseUtcThrowsExceptionOnInvalidFormat(): void
    {
        $this->expectException(DateTimeConditionException::class);
        $this->expectExceptionMessage('Invalid timestamp format');

        $this->sut->parseUtc('not-a-valid-date');
    }

    protected function _before(): void
    {
        $this->sut = new DateTimeImmutableParser();
    }
}
