<?php

namespace App\Tests\Functional\Actions;

use App\Actions\RedirectStrategy;
use App\Services\GetActionInterface;
use App\Tests\Support\FunctionalTester;

class RedirectStrategyCest
{
    public function testDi(FunctionalTester $I): void
    {
        /** @var GetActionInterface $service */
        $service = $I->grabService(GetActionInterface::class);

        $strategy = $service->getAction('redirect');

        $I->assertInstanceOf(RedirectStrategy::class, $strategy);
    }
}
