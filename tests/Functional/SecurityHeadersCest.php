<?php
/** @noinspection PhpIllegalPsrClassPathInspection */
declare( strict_types=1 );

namespace Tests\Functional;

use Tests\Support\FunctionalTester;

/**
 * Verifies that SecurityHeadersSubscriber attaches the three required
 * security headers to every response, regardless of whether the route
 * is handled by the PHP_SF router or a native Symfony controller.
 */
class SecurityHeadersCest
{

    /**
     * @dataProvider routeProvider
     */
    public function testSecurityHeadersPresent( FunctionalTester $I, \Codeception\Example $example ): void
    {
        $I->amOnPage( $example['url'] );
        $I->seeResponseCodeIs( $example['status'] );
        $I->assertResponseHeaderSame( 'x-frame-options', 'DENY' );
        $I->assertResponseHeaderSame( 'x-content-type-options', 'nosniff' );
        $I->assertResponseHeaderSame( 'referrer-policy', 'strict-origin-when-cross-origin' );
    }

    protected function routeProvider(): array
    {
        return [
            'php_sf_route'     => [ 'url' => '/',                 'status' => 200 ],
            'symfony_route'    => [ 'url' => '/example/symfony',  'status' => 200 ],
        ];
    }

}
