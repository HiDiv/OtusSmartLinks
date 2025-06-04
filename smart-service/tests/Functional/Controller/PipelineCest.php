<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller;

use App\Tests\Support\FunctionalTester;

final class PipelineCest
{
    public function getNonexistentPathReturns404(FunctionalTester $I): void
    {
        $I->amOnPage('/this-path-does-not-exist');

        $I->seeResponseCodeIs(404);
        $I->seeResponseContains('Resource not found');
    }

    public function postAnyPathReturns405(FunctionalTester $I): void
    {
        $I->sendPOST('/some-path', ['foo' => 'bar']);

        $I->seeResponseCodeIs(405);
        $I->seeHttpHeader('Allow', 'GET');
        $I->seeResponseContains('Method Not Allowed. Only GET is supported.');
    }
}
