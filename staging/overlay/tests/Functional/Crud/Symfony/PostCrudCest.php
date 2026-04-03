<?php declare( strict_types=1 );

namespace Tests\Functional\Crud\Symfony;

use App\Entity\Blog\Post;
use App\Kernel;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Assert;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Tests\Support\FunctionalTester;
use Throwable;

/**
 * Functional tests for the Symfony Post CRUD controller.
 *
 * Routes tested (prefix: /symfony/crud/posts, CSRF required):
 *   GET  /symfony/crud/posts                 → list
 *   GET  /symfony/crud/posts/create          → create form
 *   POST /symfony/crud/posts/create          → store
 *   GET  /symfony/crud/posts/{id}/edit       → edit form
 *   POST /symfony/crud/posts/{id}/edit       → update
 *   POST /symfony/crud/posts/{id}/delete     → delete
 *
 * Invalid POST re-renders the form (200), unlike PHP_SF which redirects (302).
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
            ->setTitle( 'fixture-sym-post-' . uniqid() )
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

    private function csrfToken( FunctionalTester $I ): string
    {
        return $I->grabService( CsrfTokenManagerInterface::class )
            ->getToken( 'submit' )
            ->getValue();
    }


    // ── List ─────────────────────────────────────────────────────────────────

    public function listPageLoads( FunctionalTester $I ): void
    {
        $I->amOnPage( '/symfony/crud/posts' );
        $I->seeResponseCodeIs( 200 );
        $I->seeInSource( 'Posts' );
    }


    // ── Create form ──────────────────────────────────────────────────────────

    public function createFormLoads( FunctionalTester $I ): void
    {
        $I->amOnPage( '/symfony/crud/posts/create' );
        $I->seeResponseCodeIs( 200 );
        $I->seeInSource( '<form' );
        $I->seeInSource( 'name="_token"' );
        $I->seeInSource( 'name="title"' );
        $I->seeInSource( 'name="status"' );
    }


    // ── Store ─────────────────────────────────────────────────────────────────

    public function storeCreatesEntityAndRedirectsToList( FunctionalTester $I ): void
    {
        $title = 'symcreate-' . uniqid();

        $I->amOnPage( '/symfony/crud/posts/create' );
        $I->submitForm( 'form', [
            'title'   => $title,
            'content' => 'Test body',
            'status'  => 'draft',
        ] );

        $I->seeResponseCodeIsRedirection();
        $I->followRedirect();
        $I->seeCurrentUrlContains( '/symfony/crud/posts' );

        $this->em->clear();
        $created = $this->em->getRepository( Post::class )->findOneBy( [ 'title' => $title ] );
        $I->assertNotNull( $created, 'Post should have been persisted' );

        if ( $created !== null )
            $this->createdIds[] = $created->getId();
    }

    public function storeInvalidDataRendersFormWithErrors( FunctionalTester $I ): void
    {
        $I->amOnPage( '/symfony/crud/posts/create' );
        $I->submitForm( 'form', [
            'title'  => '',
            'status' => 'invalid-status',
        ] );

        $I->seeResponseCodeIs( 200 );
        $I->seeInSource( 'alert-danger' );
    }

    public function storeWithInvalidCsrfTokenShowsError( FunctionalTester $I ): void
    {
        $I->amOnPage( '/symfony/crud/posts/create' );
        $I->submitForm( 'form', [
            '_token' => 'invalid-csrf-token',
            'title'  => 'Valid Title',
            'status' => 'draft',
        ] );

        $I->seeResponseCodeIs( 200 );
        $I->seeInSource( 'Invalid CSRF token' );
    }


    // ── Edit form ─────────────────────────────────────────────────────────────

    public function editFormLoadsWithEntityData( FunctionalTester $I ): void
    {
        $I->amOnPage( '/symfony/crud/posts/' . $this->entityId . '/edit' );
        $I->seeResponseCodeIs( 200 );
        $I->seeInSource( '<form' );
        $I->seeInSource( 'fixture-sym-post-' );
    }

    public function editNonExistentEntityRedirects( FunctionalTester $I ): void
    {
        $I->amOnPage( '/symfony/crud/posts/999999/edit' );
        $I->seeResponseCodeIsRedirection();
    }


    // ── Update ───────────────────────────────────────────────────────────────

    public function updateSavesChangesAndRedirectsToList( FunctionalTester $I ): void
    {
        $newTitle = 'symupdated-' . uniqid();

        $I->amOnPage( '/symfony/crud/posts/' . $this->entityId . '/edit' );
        $I->submitForm( 'form', [
            'title'   => $newTitle,
            'content' => 'Updated content',
            'status'  => 'published',
        ] );

        $I->seeResponseCodeIsRedirection();
        $I->followRedirect();
        $I->seeCurrentUrlContains( '/symfony/crud/posts' );

        $this->em->clear();
        $updated = $this->em->find( Post::class, $this->entityId );
        $I->assertEquals( $newTitle, $updated->getTitle() );
    }

    public function updateInvalidDataRendersFormWithErrors( FunctionalTester $I ): void
    {
        $I->amOnPage( '/symfony/crud/posts/' . $this->entityId . '/edit' );
        $I->submitForm( 'form', [
            'title'  => '',
            'status' => 'draft',
        ] );

        $I->seeResponseCodeIs( 200 );
        $I->seeInSource( 'alert-danger' );
    }


    // ── Delete ────────────────────────────────────────────────────────────────

    public function deleteRemovesEntityAndRedirectsToList( FunctionalTester $I ): void
    {
        $I->sendAjaxPostRequest( '/symfony/crud/posts/' . $this->entityId . '/delete', [
            '_token' => $this->csrfToken( $I ),
        ] );

        $I->seeResponseCodeIsRedirection();
        $I->followRedirect();
        $I->seeCurrentUrlContains( '/symfony/crud/posts' );

        $this->em->clear();
        $I->assertNull( $this->em->find( Post::class, $this->entityId ) );
    }

    public function deleteNonExistentEntityRedirects( FunctionalTester $I ): void
    {
        $I->sendAjaxPostRequest( '/symfony/crud/posts/999999/delete', [
            '_token' => $this->csrfToken( $I ),
        ] );

        $I->seeResponseCodeIsRedirection();
    }

}
