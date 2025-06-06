<?php

namespace App\Helpers;

use App\Exceptions\DateTimeConditionException;
use DateTimeImmutable;

interface DateTimeParserInterface
{
    /**
    * @throws DateTimeConditionException
    */
    public function parseUtc(string $timeString): DateTimeImmutable;
}
