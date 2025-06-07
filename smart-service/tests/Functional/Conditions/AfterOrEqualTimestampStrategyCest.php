<?php

namespace App\Tests\Functional\Conditions;

use App\Conditions\AfterOrEqualTimestampStrategy;
use App\Services\GetConditionInterface;
use App\Tests\Support\FunctionalTester;

class AfterOrEqualTimestampStrategyCest
{
    public function testDi(FunctionalTester $I): void
    {
        /** @var GetConditionInterface $service */
        $service = $I->grabService(GetConditionInterface::class);

        $strategy = $service->getCondition('after_or_equal_timestamp');

        $I->assertInstanceOf(AfterOrEqualTimestampStrategy::class, $strategy);
    }
}
