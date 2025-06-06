<?php

namespace App\Conditions;

use DateTimeImmutable;
use Symfony\Component\DependencyInjection\Attribute\AsTaggedItem;

#[AsTaggedItem('before_timestamp')]
class BeforeTimestampStrategy extends BaseTimestampCondition
{
    protected function compare(DateTimeImmutable $now, DateTimeImmutable $target): bool
    {
        return $now < $target;
    }
}
