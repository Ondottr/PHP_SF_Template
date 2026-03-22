<?php

namespace Tests\Functional;

use Tests\Support\FunctionalTester;

class ExampleFunctionalCest
{

    public function testSymfonyControllerReturnsJson(FunctionalTester $I): void
    {
        $I->amOnPage('/example/symfony');
        $I->seeResponseCodeIs(200);
        $I->seeInSource('"key":"value"');
    }

    public function testNonExistentRouteReturns404(FunctionalTester $I): void
    {
        $I->amOnPage('/this-page-does-not-exist');
        $I->seeResponseCodeIs(404);
    }

}
