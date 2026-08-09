<?php declare( strict_types=1 );

namespace Tests\Functional\Crud\PhpSf;

use Tests\Support\FunctionalTester;

/**
 * Functional tests for the PHP_SF CRUD home page (CrudHomeController).
 *
 * Routes tested:
 *   GET /crud → rendered crud_home view with links to every CRUD section
 *   (both the PHP_SF /crud/{users,posts,payments} and Symfony /symfony/crud/* variants).
 */
final class CrudHomeCest
{

    public function homePageLoads(FunctionalTester $I): void
    {
        $I->amOnPage( '/crud' );
        $I->seeResponseCodeIs( 200 );
    }

    public function homePageRendersExpectedSections(FunctionalTester $I): void
    {
        $I->amOnPage( '/crud' );
        $I->see( 'CRUD Home' );
        $I->see( 'PHP_SF Framework' );
        $I->see( 'Symfony / Twig' );
    }

    public function homePageLinksToAllSections(FunctionalTester $I): void
    {
        $I->amOnPage( '/crud' );

        // PHP_SF CRUD section links
        $I->seeElement( 'a[href="/crud/users"]' );
        $I->seeElement( 'a[href="/crud/posts"]' );
        $I->seeElement( 'a[href="/crud/payments"]' );

        // Symfony CRUD section links
        $I->seeElement( 'a[href="/symfony/crud/users"]' );
        $I->seeElement( 'a[href="/symfony/crud/posts"]' );
        $I->seeElement( 'a[href="/symfony/crud/payments"]' );
    }

}