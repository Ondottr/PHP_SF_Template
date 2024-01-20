<?php

namespace Tests\Acceptance;

use Tests\Support\AcceptanceTester;

class ExampleAcceptanceCest
{

    public function testSomething(AcceptanceTester $I): void
    {
        $I->amOnPage('/');
        $I->wait(1);
        $I->see('Haven’t registered yet');
    }

}
