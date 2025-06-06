<?php

namespace App\Tests\Unit\Actions;

use App\Actions\ResourceIsGoneStrategy;
use App\Tests\Support\UnitTester;
use Codeception\Test\Unit;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ResourceIsGoneStrategyTest extends Unit
{
    protected UnitTester $tester;

    public function testHandle(): void
    {
        $request = Request::create('/any');
        $sut = new ResourceIsGoneStrategy();

        $result = $sut->handle($request, []);

        $this->assertEquals('The resource is no longer available.', $result->getContent());
        $this->assertEquals(Response::HTTP_GONE, $result->getStatusCode());
    }
}
