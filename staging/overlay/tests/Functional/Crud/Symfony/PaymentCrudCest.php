<?php declare( strict_types=1 );

namespace Tests\Functional\Crud\Symfony;

use App\Entity\Payments\Payment;
use App\Kernel;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Assert;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Tests\Support\FunctionalTester;
use Throwable;

/**
 * Functional tests for the Symfony Payment CRUD controller.
 *
 * Routes tested (prefix: /symfony/crud/payments, CSRF required):
 *   GET  /symfony/crud/payments                 → list
 *   GET  /symfony/crud/payments/create          → create form
 *   POST /symfony/crud/payments/create          → store
 *   GET  /symfony/crud/payments/{id}/edit       → edit form
 *   POST /symfony/crud/payments/{id}/edit       → update
 *   POST /symfony/crud/payments/{id}/delete     → delete
 *
 * Invalid POST re-renders the form (200), unlike PHP_SF which redirects (302).
 */
final class PaymentCrudCest
{

    private ?EntityManagerInterface $em          = null;
    private int                     $entityId;
    private array                   $createdIds = [];


    // ── Lifecycle ────────────────────────────────────────────────────────────

    public function _before( FunctionalTester $I ): void
    {
        $em = Kernel::getInstance()->getContainer()
            ->get( 'doctrine' )
            ->getManagerForClass( Payment::class );

        if ( $em === null )
            Assert::markTestSkipped( 'No entity manager configured for ' . Payment::class );

        try {
            $em->getConnection()->executeQuery( 'SELECT 1' );
        } catch ( Throwable $e ) {
            Assert::markTestSkipped( 'DB not reachable: ' . $e->getMessage() );
        }

        $this->em         = $em;
        $this->createdIds = [];

        $fixture = ( new Payment() )
            ->setAmount( '10.00' )
            ->setCurrency( 'USD' )
            ->setStatus( 'pending' );

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
            $entity = $this->em->find( Payment::class, $id );
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
        $I->amOnPage( '/symfony/crud/payments' );
        $I->seeResponseCodeIs( 200 );
        $I->seeInSource( 'Payments' );
    }


    // ── Create form ──────────────────────────────────────────────────────────

    public function createFormLoads( FunctionalTester $I ): void
    {
        $I->amOnPage( '/symfony/crud/payments/create' );
        $I->seeResponseCodeIs( 200 );
        $I->seeInSource( '<form' );
        $I->seeInSource( 'name="_token"' );
        $I->seeInSource( 'name="amount"' );
        $I->seeInSource( 'name="currency"' );
    }


    // ── Store ─────────────────────────────────────────────────────────────────

    public function storeCreatesEntityAndRedirectsToList( FunctionalTester $I ): void
    {
        $I->amOnPage( '/symfony/crud/payments/create' );
        $I->submitForm( 'form', [
            'amount'   => '99.99',
            'currency' => 'EUR',
            'status'   => 'pending',
        ] );

        $I->seeResponseCodeIs( 200 );
        $I->seeInCurrentUrl( '/symfony/crud/payments' );

        $this->em->clear();
        $created = $this->em->getRepository( Payment::class )->findOneBy( [
            'amount'   => '99.99',
            'currency' => 'EUR',
            'status'   => 'pending',
        ] );
        $I->assertNotNull( $created, 'Payment should have been persisted' );

        if ( $created !== null )
            $this->createdIds[] = $created->getId();
    }

    public function storeInvalidDataRendersFormWithErrors( FunctionalTester $I ): void
    {
        $I->amOnPage( '/symfony/crud/payments/create' );
        $I->submitForm( 'form', [
            'amount'   => '-5',
            'currency' => 'TOOLONG',
            'status'   => 'pending',
        ] );

        $I->seeResponseCodeIs( 200 );
        $I->seeInSource( 'alert-danger' );
    }

    public function storeWithInvalidCsrfTokenShowsError( FunctionalTester $I ): void
    {
        $I->amOnPage( '/symfony/crud/payments/create' );
        $I->submitForm( 'form', [
            '_token'   => 'invalid-csrf-token',
            'amount'   => '99.99',
            'currency' => 'USD',
            'status'   => 'pending',
        ] );

        $I->seeResponseCodeIs( 200 );
        $I->seeInSource( 'Invalid CSRF token' );
    }


    // ── Edit form ─────────────────────────────────────────────────────────────

    public function editFormLoadsWithEntityData( FunctionalTester $I ): void
    {
        $I->amOnPage( '/symfony/crud/payments/' . $this->entityId . '/edit' );
        $I->seeResponseCodeIs( 200 );
        $I->seeInSource( '<form' );
        $I->seeInSource( '10.00' );
    }

    public function editNonExistentEntityRedirects( FunctionalTester $I ): void
    {
        $I->amOnPage( '/symfony/crud/payments/999999/edit' );
        $I->seeResponseCodeIs( 200 );
        $I->seeInCurrentUrl( '/symfony/crud/payments' );
    }


    // ── Update ───────────────────────────────────────────────────────────────

    public function updateSavesChangesAndRedirectsToList( FunctionalTester $I ): void
    {
        $I->amOnPage( '/symfony/crud/payments/' . $this->entityId . '/edit' );
        $I->submitForm( 'form', [
            'amount'   => '149.99',
            'currency' => 'USD',
            'status'   => 'completed',
        ] );

        $I->seeResponseCodeIs( 200 );
        $I->seeInCurrentUrl( '/symfony/crud/payments' );

        $this->em->clear();
        $updated = $this->em->find( Payment::class, $this->entityId );
        $I->assertEquals( 'completed', $updated->getStatus() );
    }

    public function updateInvalidDataRendersFormWithErrors( FunctionalTester $I ): void
    {
        $I->amOnPage( '/symfony/crud/payments/' . $this->entityId . '/edit' );
        $I->submitForm( 'form', [
            'amount'   => '-1',
            'currency' => 'USD',
            'status'   => 'pending',
        ] );

        $I->seeResponseCodeIs( 200 );
        $I->seeInSource( 'alert-danger' );
    }


    // ── Delete ────────────────────────────────────────────────────────────────

    public function deleteRemovesEntityAndRedirectsToList( FunctionalTester $I ): void
    {
        $I->amOnPage( '/symfony/crud/payments' );
        $I->sendAjaxPostRequest( '/symfony/crud/payments/' . $this->entityId . '/delete', [
            '_token' => $this->csrfToken( $I ),
        ] );

        $I->seeResponseCodeIs( 200 );
        $I->seeInCurrentUrl( '/symfony/crud/payments' );

        $this->em->clear();
        $I->assertNull( $this->em->find( Payment::class, $this->entityId ) );
    }

    public function deleteNonExistentEntityRedirects( FunctionalTester $I ): void
    {
        $I->amOnPage( '/symfony/crud/payments' );
        $I->sendAjaxPostRequest( '/symfony/crud/payments/999999/delete', [
            '_token' => $this->csrfToken( $I ),
        ] );

        $I->seeResponseCodeIs( 200 );
        $I->seeInCurrentUrl( '/symfony/crud/payments' );
    }

}
