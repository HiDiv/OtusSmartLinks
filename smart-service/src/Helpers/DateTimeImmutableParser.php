<?php

namespace App\Helpers;

use App\Exceptions\DateTimeConditionException;
use DateTimeImmutable;
use DateTimeZone;
use Throwable;

class DateTimeImmutableParser implements DateTimeParserInterface
{
    public function parseUtc(string $timeString): DateTimeImmutable
    {
        try {
            return (bool) preg_match('/(Z|[+\-]\d{2}:\d{2})$/', $timeString)
                ? (new DateTimeImmutable($timeString))->setTimezone(new DateTimeZone('UTC'))
                : new DateTimeImmutable($timeString, new DateTimeZone('UTC'));
        } catch (Throwable $e) {
            throw new DateTimeConditionException('Invalid timestamp format');
        }
    }
}
