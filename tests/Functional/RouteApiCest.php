<?php declare(strict_types=1);

namespace Tests\Functional;

use Tests\Support\FunctionalTester;

class RouteApiCest
{
    public function testRouteApiEndpointReturnsJson(FunctionalTester $I): void
    {
        $I->amOnPage('/example/api/status');
        $I->seeResponseCodeIs(200);
        $I->seeInSource('{"status":"ok"}');
    }

    public function testRouteApiResponseSkipsLayout(FunctionalTester $I): void
    {
        $I->amOnPage('/example/api/plain');
        $I->seeResponseCodeIs(200);
        $I->seeInSource('plain api response without layout');

        // Framework chrome skipped — the route is marked #[RouteApi]
        $I->dontSeeInSource(ChromeMarkers::HEADER);
        $I->dontSeeInSource(ChromeMarkers::FOOTER);
    }
}
