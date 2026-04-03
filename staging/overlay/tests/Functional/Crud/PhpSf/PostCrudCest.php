<?php declare( strict_types=1 );

/** @noinspection PhpUnhandledExceptionInspection */

namespace Tests\Functional\Crud\PhpSf;

use App\Entity\Blog\Post;
use App\Kernel;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Assert;
use Tests\Support\FunctionalTester;
use Throwable;

/**
 * Functional tests for the PHP_SF Post CRUD controller.
 *
 * Routes tested (prefix: /crud/posts, no CSRF):
 *   GET  /crud/posts                 → list
 *   GET  /crud/posts/create          → create form
 *   POST /crud/posts/create          → store
 *   GET  /crud/posts/{id}/edit       → edit form
 *   POST /crud/posts/{id}/edit       → update
 *   POST /crud/posts/{id}/delete     → delete
 *
 * PHP_SF controllers never call setContent(), so Response content is always
 * empty in the functional test context. All responses (including redirects)
 * return HTTP 200 — PHP_SF uses an in-process redirect via Router::init(),
 * not an HTTP 302. Tests therefore assert only status codes and DB side effects.
 */
final class PostCrudCest
{

    private ?EntityManagerInterface $em          = null;
    private int                     $entityId;
    private array                   $createdIds = [];


    // ── Lifecycle ────────────────────────────────────────────────────────────

    public function _before( FunctionalTester $I ): void
    {
        $em = Kernel::getInstance()->getContainer()
            ->get( 'doctrine' )
            ->getManagerForClass( Post::class );

        if ( $em === null )
            Assert::markTestSkipped( 'No entity manager configured for ' . Post::class );

        try {
            $em->getConnection()->executeQuery( 'SELECT 1' );
        } catch ( Throwable $e ) {
            Assert::markTestSkipped( 'DB not reachable: ' . $e->getMessage() );
        }

        $this->em         = $em;
        $this->createdIds = [];

        $fixture = ( new Post() )
            ->setTitle( 'fixture-phpsf-post-' . uniqid() )
            ->setContent( 'Fixture content' )
            ->setStatus( 'draft' );

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
            $entity = $this->em->find( Post::class, $id );
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
        $I->amOnPage( '/crud/posts' );
        $I->seeResponseCodeIs( 200 );
    }


    // ── Create form ──────────────────────────────────────────────────────────

    public function createFormLoads( FunctionalTester $I ): void
    {
        $I->amOnPage( '/crud/posts/create' );
        $I->seeResponseCodeIs( 200 );
    }


    // ── Store ─────────────────────────────────────────────────────────────────

    public function storeCreatesEntity( FunctionalTester $I ): void
    {
        $title = 'phpsfcreate-' . uniqid();

        $I->sendAjaxPostRequest( '/crud/posts/create', [
            'title'   => $title,
            'content' => 'Test body',
            'status'  => 'draft',
        ] );

        $I->seeResponseCodeIs( 200 );

        $this->em->clear();
        $created = $this->em->getRepository( Post::class )->findOneBy( [ 'title' => $title ] );
        $I->assertNotNull( $created, 'Post should have been persisted' );

        if ( $created !== null )
            $this->createdIds[] = $created->getId();
    }

    public function storeInvalidDataDoesNotPersistEntity( FunctionalTester $I ): void
    {
        $I->sendAjaxPostRequest( '/crud/posts/create', [
            'title'  => '',
            'status' => 'invalid-status',
        ] );

        $I->seeResponseCodeIs( 200 );

        $this->em->clear();
        $I->assertNull(
            $this->em->getRepository( Post::class )->findOneBy( [ 'title' => '' ] ),
            'Invalid post should not have been persisted'
        );
    }


    // ── Edit form ─────────────────────────────────────────────────────────────

    public function editFormLoadsWithEntityData( FunctionalTester $I ): void
    {
        $I->amOnPage( '/crud/posts/' . $this->entityId . '/edit' );
        $I->seeResponseCodeIs( 200 );
    }

    public function editNonExistentEntityReturns200( FunctionalTester $I ): void
    {
        $I->amOnPage( '/crud/posts/999999/edit' );
        $I->seeResponseCodeIs( 200 );
    }


    // ── Update ───────────────────────────────────────────────────────────────

    public function updateSavesChanges( FunctionalTester $I ): void
    {
        $newTitle = 'phpsfupdated-' . uniqid();

        $I->sendAjaxPostRequest( '/crud/posts/' . $this->entityId . '/edit', [
            'title'   => $newTitle,
            'content' => 'Updated content',
            'status'  => 'published',
        ] );

        $I->seeResponseCodeIs( 200 );

        $this->em->clear();
        $updated = $this->em->find( Post::class, $this->entityId );
        $I->assertEquals( $newTitle, $updated->getTitle() );
    }

    public function updateInvalidDataDoesNotSaveChanges( FunctionalTester $I ): void
    {
        $originalTitle = $this->em->find( Post::class, $this->entityId )->getTitle();

        $I->sendAjaxPostRequest( '/crud/posts/' . $this->entityId . '/edit', [
            'title'  => '',
            'status' => 'draft',
        ] );

        $I->seeResponseCodeIs( 200 );

        $this->em->clear();
        $post = $this->em->find( Post::class, $this->entityId );
        $I->assertEquals( $originalTitle, $post->getTitle(), 'Title should not have changed on invalid input' );
    }


    // ── Delete ────────────────────────────────────────────────────────────────

    public function deleteRemovesEntity( FunctionalTester $I ): void
    {
        $I->sendAjaxPostRequest( '/crud/posts/' . $this->entityId . '/delete', [] );
        $I->seeResponseCodeIs( 200 );

        $this->em->clear();
        $I->assertNull( $this->em->find( Post::class, $this->entityId ) );
    }

    public function deleteNonExistentEntityReturns200( FunctionalTester $I ): void
    {
        $I->sendAjaxPostRequest( '/crud/posts/999999/delete', [] );
        $I->seeResponseCodeIs( 200 );
    }

}
