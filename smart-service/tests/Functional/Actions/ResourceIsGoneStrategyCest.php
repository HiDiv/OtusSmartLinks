<?php

namespace App\Tests\Functional\Actions;

use App\Actions\ResourceIsGoneStrategy;
use App\Services\GetActionInterface;
use App\Tests\Support\FunctionalTester;

class ResourceIsGoneStrategyCest
{
    public function testDi(FunctionalTester $I): void
    {
        /** @var GetActionInterface $service */
        $service = $I->grabService(GetActionInterface::class);

        $strategy = $service->getAction('gone');

        $I->assertInstanceOf(ResourceIsGoneStrategy::class, $strategy);
    }
}
