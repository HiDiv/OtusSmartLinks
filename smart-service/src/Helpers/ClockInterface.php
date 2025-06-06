<?php

namespace App\Helpers;

use DateTimeImmutable;

interface ClockInterface
{
    public function nowUtc(): DateTimeImmutable;
}
