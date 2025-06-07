<?php

namespace App\Tests\Functional\Conditions;

use App\Conditions\BeforeTimestampStrategy;
use App\Services\GetConditionInterface;
use App\Tests\Support\FunctionalTester;

class BeforeTimestampStrategyCest
{
    public function testDi(FunctionalTester $I): void
    {
        /** @var GetConditionInterface $service */
        $service = $I->grabService(GetConditionInterface::class);

        $strategy = $service->getCondition('before_timestamp');

        $I->assertInstanceOf(BeforeTimestampStrategy::class, $strategy);
    }
}
