<?php

namespace App\Helpers;

use DateTimeImmutable;
use DateTimeZone;

class SystemClock implements ClockInterface
{
    public function nowUtc(): DateTimeImmutable
    {
        return new DateTimeImmutable('now', new DateTimeZone('UTC'));
    }
}
