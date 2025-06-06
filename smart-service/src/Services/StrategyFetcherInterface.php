<?php

namespace App\Services;

use App\Entity\Strategy;

interface StrategyFetcherInterface
{
    /** @return Strategy[] */
    public function fetchForPath(string $path): array;
}
