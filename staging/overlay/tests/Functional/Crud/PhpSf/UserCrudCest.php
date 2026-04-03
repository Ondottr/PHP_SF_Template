<?php declare( strict_types=1 );

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
 *
 * PHP_SF controllers never call setContent(), so Response content is always
 * empty in the functional test context. All responses (including redirects)
 * return HTTP 200 — PHP_SF uses an in-process redirect via Router::init(),
 * not an HTTP 302. Tests therefore assert only status codes and DB side effects.
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
            ->setEmail( 'fixture-phpsf-user-' . uniqid() . '@example.com' )
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


    // ── List ─────────────────────────────────────────────────────────────────

    public function listPageLoads( FunctionalTester $I ): void
    {
        $I->amOnPage( '/crud/users' );
        $I->seeResponseCodeIs( 200 );
    }


    // ── Create form ──────────────────────────────────────────────────────────

    public function createFormLoads( FunctionalTester $I ): void
    {
        $I->amOnPage( '/crud/users/create' );
        $I->seeResponseCodeIs( 200 );
    }


    // ── Store ─────────────────────────────────────────────────────────────────

    public function storeCreatesEntity( FunctionalTester $I ): void
    {
        $email = 'phpsfcreate-' . uniqid() . '@example.com';

        $I->sendAjaxPostRequest( '/crud/users/create', [
            'email'    => $email,
            'password' => 'TestPass123',
        ] );

        $I->seeResponseCodeIs( 200 );

        $this->em->clear();
        $created = $this->em->getRepository( User::class )->findOneBy( [ 'email' => $email ] );
        $I->assertNotNull( $created, 'User should have been persisted' );

        if ( $created !== null )
            $this->createdIds[] = $created->getId();
    }

    public function storeInvalidDataDoesNotPersistEntity( FunctionalTester $I ): void
    {
        $I->sendAjaxPostRequest( '/crud/users/create', [
            'email'    => 'not-a-valid-email',
            'password' => 'TestPass123',
        ] );

        $I->seeResponseCodeIs( 200 );

        $this->em->clear();
        $I->assertNull(
            $this->em->getRepository( User::class )->findOneBy( [ 'email' => 'not-a-valid-email' ] ),
            'Invalid user should not have been persisted'
        );
    }


    // ── Edit form ─────────────────────────────────────────────────────────────

    public function editFormLoadsWithEntityData( FunctionalTester $I ): void
    {
        $I->amOnPage( '/crud/users/' . $this->entityId . '/edit' );
        $I->seeResponseCodeIs( 200 );
    }

    public function editNonExistentEntityReturns200( FunctionalTester $I ): void
    {
        $I->amOnPage( '/crud/users/999999/edit' );
        $I->seeResponseCodeIs( 200 );
    }


    // ── Update ───────────────────────────────────────────────────────────────

    public function updateSavesChanges( FunctionalTester $I ): void
    {
        $newEmail = 'phpsfupdated-' . uniqid() . '@example.com';

        $I->sendAjaxPostRequest( '/crud/users/' . $this->entityId . '/edit', [
            'email'    => $newEmail,
            'password' => '',
        ] );

        $I->seeResponseCodeIs( 200 );

        $this->em->clear();
        $updated = $this->em->find( User::class, $this->entityId );
        $I->assertEquals( $newEmail, $updated->getEmail() );
    }

    public function updateInvalidDataDoesNotSaveChanges( FunctionalTester $I ): void
    {
        $originalEmail = $this->em->find( User::class, $this->entityId )->getEmail();

        $I->sendAjaxPostRequest( '/crud/users/' . $this->entityId . '/edit', [
            'email'    => 'not-a-valid-email',
            'password' => '',
        ] );

        $I->seeResponseCodeIs( 200 );

        $this->em->clear();
        $user = $this->em->find( User::class, $this->entityId );
        $I->assertEquals( $originalEmail, $user->getEmail(), 'Email should not have changed on invalid input' );
    }


    // ── Delete ────────────────────────────────────────────────────────────────

    public function deleteRemovesEntity( FunctionalTester $I ): void
    {
        $I->sendAjaxPostRequest( '/crud/users/' . $this->entityId . '/delete', [] );
        $I->seeResponseCodeIs( 200 );

        $this->em->clear();
        $I->assertNull( $this->em->find( User::class, $this->entityId ) );
    }

    public function deleteNonExistentEntityReturns200( FunctionalTester $I ): void
    {
        $I->sendAjaxPostRequest( '/crud/users/999999/delete', [] );
        $I->seeResponseCodeIs( 200 );
    }

}
