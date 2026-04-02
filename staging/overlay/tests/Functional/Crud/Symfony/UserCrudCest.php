<?php declare( strict_types=1 );

namespace Tests\Functional\Crud\Symfony;

use App\Entity\Main\User;
use App\Kernel;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Assert;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Tests\Support\FunctionalTester;
use Throwable;

/**
 * Functional tests for the Symfony User CRUD controller.
 *
 * Routes tested (prefix: /symfony/crud/users, CSRF required):
 *   GET  /symfony/crud/users                 → list
 *   GET  /symfony/crud/users/create          → create form
 *   POST /symfony/crud/users/create          → store
 *   GET  /symfony/crud/users/{id}/edit       → edit form
 *   POST /symfony/crud/users/{id}/edit       → update
 *   POST /symfony/crud/users/{id}/delete     → delete
 *
 * Invalid POST re-renders the form (200), unlike PHP_SF which redirects (302).
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

        $this->em         = $em;
        $this->createdIds = [];

        $fixture = ( new User() )
            ->setEmail( 'fixture-sym-user-' . uniqid() . '@example.com' )
            ->setPassword( 'FixturePass1' );

        $this->em->persist( $fixture );
        $this->em->flush();

        $this->entityId     = $fixture->getId();
        $this->createdIds[] = $this->entityId;
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

    private function csrfToken( FunctionalTester $I ): string
    {
        return $I->grabService( CsrfTokenManagerInterface::class )
            ->getToken( 'submit' )
            ->getValue();
    }


    // ── List ─────────────────────────────────────────────────────────────────

    public function listPageLoads( FunctionalTester $I ): void
    {
        $I->amOnPage( '/symfony/crud/users' );
        $I->seeResponseCodeIs( 200 );
        $I->seeInSource( 'Users' );
    }


    // ── Create form ──────────────────────────────────────────────────────────

    public function createFormLoads( FunctionalTester $I ): void
    {
        $I->amOnPage( '/symfony/crud/users/create' );
        $I->seeResponseCodeIs( 200 );
        $I->seeInSource( '<form' );
        $I->seeInSource( 'name="_token"' );
        $I->seeInSource( 'name="email"' );
        $I->seeInSource( 'name="password"' );
    }


    // ── Store ─────────────────────────────────────────────────────────────────

    public function storeCreatesEntityAndRedirectsToList( FunctionalTester $I ): void
    {
        $email = 'symcreate-' . uniqid() . '@example.com';

        $I->sendPost( '/symfony/crud/users/create', [
            '_token'   => $this->csrfToken( $I ),
            'email'    => $email,
            'password' => 'TestPass123',
        ] );

        $I->seeResponseCodeIsRedirection();
        $I->followRedirect();
        $I->seeCurrentUrlContains( '/symfony/crud/users' );

        $this->em->clear();
        $created = $this->em->getRepository( User::class )->findOneBy( [ 'email' => $email ] );
        $I->assertNotNull( $created, 'User should have been persisted' );

        if ( $created !== null )
            $this->createdIds[] = $created->getId();
    }

    public function storeInvalidDataRendersFormWithErrors( FunctionalTester $I ): void
    {
        $I->sendPost( '/symfony/crud/users/create', [
            '_token'   => $this->csrfToken( $I ),
            'email'    => 'not-a-valid-email',
            'password' => 'TestPass123',
        ] );

        $I->seeResponseCodeIs( 200 );
        $I->seeInSource( 'alert-danger' );
    }

    public function storeWithInvalidCsrfTokenShowsError( FunctionalTester $I ): void
    {
        $I->sendPost( '/symfony/crud/users/create', [
            '_token'   => 'invalid-csrf-token',
            'email'    => 'valid@example.com',
            'password' => 'TestPass123',
        ] );

        $I->seeResponseCodeIs( 200 );
        $I->seeInSource( 'Invalid CSRF token' );
    }


    // ── Edit form ─────────────────────────────────────────────────────────────

    public function editFormLoadsWithEntityData( FunctionalTester $I ): void
    {
        $I->amOnPage( '/symfony/crud/users/' . $this->entityId . '/edit' );
        $I->seeResponseCodeIs( 200 );
        $I->seeInSource( '<form' );
        $I->seeInSource( 'fixture-sym-user-' );
    }

    public function editNonExistentEntityRedirects( FunctionalTester $I ): void
    {
        $I->amOnPage( '/symfony/crud/users/999999/edit' );
        $I->seeResponseCodeIsRedirection();
    }


    // ── Update ───────────────────────────────────────────────────────────────

    public function updateSavesChangesAndRedirectsToList( FunctionalTester $I ): void
    {
        $newEmail = 'symupdated-' . uniqid() . '@example.com';

        $I->sendPost( '/symfony/crud/users/' . $this->entityId . '/edit', [
            '_token'   => $this->csrfToken( $I ),
            'email'    => $newEmail,
            'password' => '',
        ] );

        $I->seeResponseCodeIsRedirection();
        $I->followRedirect();
        $I->seeCurrentUrlContains( '/symfony/crud/users' );

        $this->em->clear();
        $updated = $this->em->find( User::class, $this->entityId );
        $I->assertEquals( $newEmail, $updated->getEmail() );
    }

    public function updateInvalidDataRendersFormWithErrors( FunctionalTester $I ): void
    {
        $I->sendPost( '/symfony/crud/users/' . $this->entityId . '/edit', [
            '_token'   => $this->csrfToken( $I ),
            'email'    => 'not-a-valid-email',
            'password' => '',
        ] );

        $I->seeResponseCodeIs( 200 );
        $I->seeInSource( 'alert-danger' );
    }


    // ── Delete ────────────────────────────────────────────────────────────────

    public function deleteRemovesEntityAndRedirectsToList( FunctionalTester $I ): void
    {
        $I->sendAjaxPostRequest( '/symfony/crud/users/' . $this->entityId . '/delete', [
            '_token' => $this->csrfToken( $I ),
        ] );

        $I->seeInCurrentUrl( '/symfony/crud/users' );

        $this->em->clear();
        $I->assertNull($this->em->find(User::class, $this->entityId));
    }

}
