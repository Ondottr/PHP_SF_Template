<?php declare( strict_types=1 );

/** @noinspection PhpUnhandledExceptionInspection */

namespace Tests\Functional\Crud\PhpSf;

use App\Entity\Main\User;
use App\Kernel;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Assert;
use Tests\Support\FunctionalTester;
use Throwable;

/**
 * Functional tests for the PHP_SF User CRUD controller.
 *
 * Routes tested (prefix: /crud/users, no CSRF):
 *   GET  /crud/users                 → list
 *   GET  /crud/users/create          → create form
 *   POST /crud/users/create          → store
 *   GET  /crud/users/{id}/edit       → edit form
 *   POST /crud/users/{id}/edit       → update
 *   POST /crud/users/{id}/delete     → delete
 */
final class UserCrudCest
{

    private ?EntityManagerInterface $em          = null;
    private int                     $entityId;
    private array                   $createdIds = [];


    // ── Lifecycle ────────────────────────────────────────────────────────────

    public function _before( FunctionalTester $I ): void
    {
        $em = Kernel::getInstance()->getContainer()
            ->get( 'doctrine' )
            ->getManagerForClass( User::class );

        if ( $em === null )
            Assert::markTestSkipped( 'No entity manager configured for ' . User::class );

        try {
            $em->getConnection()->executeQuery( 'SELECT 1' );
        } catch ( Throwable $e ) {
            Assert::markTestSkipped( 'DB not reachable: ' . $e->getMessage() );
        }

        $this->em          = $em;
        $this->createdIds  = [];

        $fixture = ( new User() )
            ->setEmail( 'fixture-phpsf-user-' . uniqid() . '@example.com' )
            ->setPassword( 'FixturePass1' );

        $this->em->persist( $fixture );
        $this->em->flush();

        $this->entityId      = $fixture->getId();
        $this->createdIds[]  = $this->entityId;
    }

    public function _after( FunctionalTester $I ): void
    {
        if ( $this->em === null )
            return;

        $this->em->clear();

        foreach ( $this->createdIds as $id ) {
            $entity = $this->em->find( User::class, $id );
            if ( $entity !== null ) {
                $this->em->remove( $entity );
                $this->em->flush();
            }
        }

        $this->createdIds = [];
    }


    // ── List ─────────────────────────────────────────────────────────────────

    public function listPageLoads( FunctionalTester $I ): void
    {
        $I->amOnPage( '/crud/users' );
        $I->seeResponseCodeIs( 200 );
        $I->seeInSource( 'Users' );
    }


    // ── Create form ──────────────────────────────────────────────────────────

    public function createFormLoads( FunctionalTester $I ): void
    {
        $I->amOnPage( '/crud/users/create' );
        $I->seeResponseCodeIs( 200 );
        $I->seeInSource( '<form' );
        $I->seeInSource( 'name="email"' );
        $I->seeInSource( 'name="password"' );
    }


    // ── Store ─────────────────────────────────────────────────────────────────

    public function storeCreatesEntityAndRedirectsToList( FunctionalTester $I ): void
    {
        $email = 'phpsfcreate-' . uniqid() . '@example.com';

        $I->sendPost( '/crud/users/create', [
            'email'    => $email,
            'password' => 'TestPass123',
        ] );

        $I->seeResponseCodeIsRedirection();
        $I->followRedirect();
        $I->seeCurrentUrlContains( '/crud/users' );

        $this->em->clear();
        $created = $this->em->getRepository( User::class )->findOneBy( [ 'email' => $email ] );
        $I->assertNotNull( $created, 'User should have been persisted' );

        if ( $created !== null )
            $this->createdIds[] = $created->getId();
    }

    public function storeInvalidDataRedirectsBackWithErrors( FunctionalTester $I ): void
    {
        $I->amOnPage( '/crud/users/create' );
        $I->seeResponseCodeIs( 200 );

        $I->submitForm( 'form', [
            'email'    => 'not-a-valid-email',
            'password' => 'TestPass123',
        ] );

        $I->seeResponseCodeIsRedirection();
        $I->followRedirect();
        $I->seeInSource( 'alert-danger' );
    }


    // ── Edit form ─────────────────────────────────────────────────────────────

    public function editFormLoadsWithEntityData( FunctionalTester $I ): void
    {
        $I->amOnPage( '/crud/users/' . $this->entityId . '/edit' );
        $I->seeResponseCodeIs( 200 );
        $I->seeInSource( '<form' );
        $I->seeInSource( 'fixture-phpsf-user-' );
    }

    public function editNonExistentEntityRedirects( FunctionalTester $I ): void
    {
        $I->amOnPage( '/crud/users/999999/edit' );
        $I->seeResponseCodeIsRedirection();
    }


    // ── Update ───────────────────────────────────────────────────────────────

    public function updateSavesChangesAndRedirectsToList( FunctionalTester $I ): void
    {
        $newEmail = 'phpsfupdated-' . uniqid() . '@example.com';

        $I->sendPost( '/crud/users/' . $this->entityId . '/edit', [
            'email'    => $newEmail,
            'password' => '',
        ] );

        $I->seeResponseCodeIsRedirection();
        $I->followRedirect();
        $I->seeCurrentUrlContains( '/crud/users' );

        $this->em->clear();
        $updated = $this->em->find( User::class, $this->entityId );
        $I->assertEquals( $newEmail, $updated->getEmail() );
    }

    public function updateInvalidDataRedirectsBackWithErrors( FunctionalTester $I ): void
    {
        $I->amOnPage( '/crud/users/' . $this->entityId . '/edit' );
        $I->seeResponseCodeIs( 200 );

        $I->submitForm( 'form', [
            'email'    => 'not-a-valid-email',
            'password' => '',
        ] );

        $I->seeResponseCodeIsRedirection();
        $I->followRedirect();
        $I->seeInSource( 'alert-danger' );
    }


    // ── Delete ────────────────────────────────────────────────────────────────

    public function deleteRemovesEntityAndRedirectsToList( FunctionalTester $I ): void
    {
        $I->sendPost( '/crud/users/' . $this->entityId . '/delete' );
        $I->seeResponseCodeIsRedirection();
        $I->followRedirect();
        $I->seeCurrentUrlContains( '/crud/users' );

        $this->em->clear();
        $I->assertNull( $this->em->find( User::class, $this->entityId ) );
    }

    public function deleteNonExistentEntityRedirects( FunctionalTester $I ): void
    {
        $I->sendPost( '/crud/users/999999/delete' );
        $I->seeResponseCodeIsRedirection();
    }

}
