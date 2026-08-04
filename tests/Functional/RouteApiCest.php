<?php declare(strict_types=1);

namespace Tests\Functional;

use Tests\Support\FunctionalTester;

class RouteApiCest
{
    private const CHROME_HEADER_MARKER = 'header header_elements row';

    private const CHROME_FOOTER_MARKER = '<div class="footer">';


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
        $I->dontSeeInSource(self::CHROME_HEADER_MARKER);
        $I->dontSeeInSource(self::CHROME_FOOTER_MARKER);
    }
}
