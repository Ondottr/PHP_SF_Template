<?php declare(strict_types=1);

namespace Tests\Acceptance;

use Tests\Support\AcceptanceTester;

class ExampleAcceptanceCest
{
    public function testWelcomePageLoads(AcceptanceTester $I): void
    {
        $I->amOnPage('/');
        $I->see('Welcome');
    }

    public function testWelcomePageHasReadyMessage(AcceptanceTester $I): void
    {
        $I->amOnPage('/');
        $I->see('Your application is now ready');
    }
}
