<?php


namespace Tests\Unit;

use PHP_SF\System\Core\Cache\RedisCacheAdapter;
use Tests\Support\Uni2Tester;

class ExampleUnitCest
{

    public function testSomething(Uni2Tester $I): void
    {
        $I->assertInstanceOf(RedisCacheAdapter::class, ca());
    }

}
