<?php

namespace App\Conditions;

use DateTimeImmutable;
use Symfony\Component\DependencyInjection\Attribute\AsTaggedItem;

#[AsTaggedItem('after_or_equal_timestamp')]
class AfterOrEqualTimestampStrategy extends BaseTimestampCondition
{
    protected function compare(DateTimeImmutable $now, DateTimeImmutable $target): bool
    {
        return $now >= $target;
    }
}
