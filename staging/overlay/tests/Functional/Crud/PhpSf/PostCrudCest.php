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
        $I->seeInSource( 'Posts' );
    }


    // ── Create form ──────────────────────────────────────────────────────────

    public function createFormLoads( FunctionalTester $I ): void
    {
        $I->amOnPage( '/crud/posts/create' );
        $I->seeResponseCodeIs( 200 );
        $I->seeInSource( '<form' );
        $I->seeInSource( 'name="title"' );
        $I->seeInSource( 'name="status"' );
    }


    // ── Store ─────────────────────────────────────────────────────────────────

    public function storeCreatesEntityAndRedirectsToList( FunctionalTester $I ): void
    {
        $title = 'phpsfcreate-' . uniqid();

        $I->sendPost( '/crud/posts/create', [
            'title'   => $title,
            'content' => 'Test body',
            'status'  => 'draft',
        ] );

        $I->seeResponseCodeIsRedirection();
        $I->followRedirect();
        $I->seeCurrentUrlContains( '/crud/posts' );

        $this->em->clear();
        $created = $this->em->getRepository( Post::class )->findOneBy( [ 'title' => $title ] );
        $I->assertNotNull( $created, 'Post should have been persisted' );

        if ( $created !== null )
            $this->createdIds[] = $created->getId();
    }

    public function storeInvalidDataRedirectsBackWithErrors( FunctionalTester $I ): void
    {
        $I->amOnPage( '/crud/posts/create' );
        $I->seeResponseCodeIs( 200 );

        $I->submitForm( 'form', [
            'title'  => '',
            'status' => 'invalid-status',
        ] );

        $I->seeResponseCodeIsRedirection();
        $I->followRedirect();
        $I->seeInSource( 'alert-danger' );
    }


    // ── Edit form ─────────────────────────────────────────────────────────────

    public function editFormLoadsWithEntityData( FunctionalTester $I ): void
    {
        $I->amOnPage( '/crud/posts/' . $this->entityId . '/edit' );
        $I->seeResponseCodeIs( 200 );
        $I->seeInSource( '<form' );
        $I->seeInSource( 'fixture-phpsf-post-' );
    }

    public function editNonExistentEntityRedirects( FunctionalTester $I ): void
    {
        $I->amOnPage( '/crud/posts/999999/edit' );
        $I->seeResponseCodeIsRedirection();
    }


    // ── Update ───────────────────────────────────────────────────────────────

    public function updateSavesChangesAndRedirectsToList( FunctionalTester $I ): void
    {
        $newTitle = 'phpsfupdated-' . uniqid();

        $I->sendPost( '/crud/posts/' . $this->entityId . '/edit', [
            'title'   => $newTitle,
            'content' => 'Updated content',
            'status'  => 'published',
        ] );

        $I->seeResponseCodeIsRedirection();
        $I->followRedirect();
        $I->seeCurrentUrlContains( '/crud/posts' );

        $this->em->clear();
        $updated = $this->em->find( Post::class, $this->entityId );
        $I->assertEquals( $newTitle, $updated->getTitle() );
    }

    public function updateInvalidDataRedirectsBackWithErrors( FunctionalTester $I ): void
    {
        $I->amOnPage( '/crud/posts/' . $this->entityId . '/edit' );
        $I->seeResponseCodeIs( 200 );

        $I->submitForm( 'form', [
            'title'  => '',
            'status' => 'draft',
        ] );

        $I->seeResponseCodeIsRedirection();
        $I->followRedirect();
        $I->seeInSource( 'alert-danger' );
    }


    // ── Delete ────────────────────────────────────────────────────────────────

    public function deleteRemovesEntityAndRedirectsToList( FunctionalTester $I ): void
    {
        $I->sendPost( '/crud/posts/' . $this->entityId . '/delete' );
        $I->seeResponseCodeIsRedirection();
        $I->followRedirect();
        $I->seeCurrentUrlContains( '/crud/posts' );

        $this->em->clear();
        $I->assertNull( $this->em->find( Post::class, $this->entityId ) );
    }

    public function deleteNonExistentEntityRedirects( FunctionalTester $I ): void
    {
        $I->sendPost( '/crud/posts/999999/delete' );
        $I->seeResponseCodeIsRedirection();
    }

}
