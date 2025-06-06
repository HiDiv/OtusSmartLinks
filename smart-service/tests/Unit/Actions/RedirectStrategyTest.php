<?php

namespace App\Tests\Unit\Actions;

use App\Actions\RedirectStrategy;
use App\Exceptions\DateTimeConditionException;
use App\Tests\Support\UnitTester;
use Codeception\Test\Unit;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class RedirectStrategyTest extends Unit
{
    protected UnitTester $tester;
    protected RedirectStrategy $sut;

    public static function providerInvalidParams(): array
    {
        return [
            'missing url key' => [
                'params' => [],
                'message' => "Parameter 'url' is missing",
            ],
            'url not a string' => [
                'params' => ['url' => 123],
                'message' => "Parameter 'url' must be not empty string",
            ],
            'url empty string' => [
                'params' => ['url' => ''],
                'message' => "Parameter 'url' must be not empty string",
            ],
        ];
    }

    /**
     * @dataProvider providerInvalidParams
     */
    public function testHandleThrowsExceptionForInvalidParams(array $params, string $message): void
    {
        $request = Request::create('/any');

        $this->expectException(DateTimeConditionException::class);
        $this->expectExceptionMessage($message);

        $this->sut->handle($request, $params);
    }

    public function testHandleReturnsRedirectResponseOnValidUrl(): void
    {
        $validUrl = 'https://example.com/path';
        $params = ['url' => $validUrl];
        $request = Request::create('/any');

        $result = $this->sut->handle($request, $params);

        $this->assertEquals(Response::HTTP_FOUND, $result->getStatusCode());
        $this->assertEquals($validUrl, $result->getTargetUrl());
        $this->assertTrue($result->headers->has('Location'));
        $this->assertEquals($validUrl, $result->headers->get('Location'));
    }

    protected function _before(): void
    {
        $this->sut = new RedirectStrategy();
    }
}
