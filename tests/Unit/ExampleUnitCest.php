<?php

namespace Tests\Unit;

use PHP_SF\System\Core\Cache\RedisCacheAdapter;
use Tests\Support\Uni2Tester;

class ExampleUnitCest
{

    public function _after(Uni2Tester $I): void
    {
        rca()->clear();
    }


    public function testRedisInstanceIsSingleton(Uni2Tester $I): void
    {
        $I->assertInstanceOf(RedisCacheAdapter::class, rca());
        $I->assertSame(rca(), rca());
    }

    public function testRedisSetAndGet(Uni2Tester $I): void
    {
        rca()->set('example_key', 'example_value');

        $I->assertEquals('example_value', rca()->get('example_key'));
    }

    public function testRedisHasAndDelete(Uni2Tester $I): void
    {
        rca()->set('example_key', 'example_value');
        $I->assertTrue(rca()->has('example_key'));

        rca()->delete('example_key');
        $I->assertFalse(rca()->has('example_key'));
    }

    public function testRedisMissingKeyReturnsNull(Uni2Tester $I): void
    {
        $I->assertNull(rca()->get('non_existing_key'));
    }

}
