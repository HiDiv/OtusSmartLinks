<?php

namespace App\Conditions;

use App\Exceptions\DateTimeConditionException;
use App\Helpers\ClockInterface;
use App\Helpers\DateTimeParserInterface;
use DateTimeImmutable;
use Symfony\Component\HttpFoundation\Request;

abstract class BaseTimestampCondition implements ConditionStrategyInterface
{
    public function __construct(
        private readonly DateTimeParserInterface $parser,
        private readonly ClockInterface $clock,
    ) {
    }

    public function check(Request $request, array $params): bool
    {
        if (!array_key_exists('timestamp', $params)) {
            throw new DateTimeConditionException("Parameter 'timestamp' is missing");
        }

        if (!is_string($params['timestamp']) || empty($params['timestamp'])) {
            throw new DateTimeConditionException("Parameter 'timestamp' must be not empty string");
        }

        $targetDateTime = $this->parser->parseUtc($params['timestamp']);
        $nowUtc = $this->clock->nowUtc();

        return $this->compare($nowUtc, $targetDateTime);
    }

    abstract protected function compare(DateTimeImmutable $now, DateTimeImmutable $target): bool;
}
