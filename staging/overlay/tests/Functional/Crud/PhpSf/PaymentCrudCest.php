<?php declare( strict_types=1 );

namespace Tests\Functional\Crud\PhpSf;

use App\Entity\Payments\Payment;
use App\Kernel;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Assert;
use Tests\Support\FunctionalTester;
use Throwable;

/**
 * Functional tests for the PHP_SF Payment CRUD controller.
 *
 * Routes tested (prefix: /crud/payments, no CSRF):
 *   GET  /crud/payments                 → list
 *   GET  /crud/payments/create          → create form
 *   POST /crud/payments/create          → store
 *   GET  /crud/payments/{id}/edit       → edit form
 *   POST /crud/payments/{id}/edit       → update
 *   POST /crud/payments/{id}/delete     → delete
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


    // ── List ─────────────────────────────────────────────────────────────────

    public function listPageLoads( FunctionalTester $I ): void
    {
        $I->amOnPage( '/crud/payments' );
        $I->seeResponseCodeIs( 200 );
        $I->see( 'Payments' );
    }


    // ── Create form ──────────────────────────────────────────────────────────

    public function createFormLoads( FunctionalTester $I ): void
    {
        $I->amOnPage( '/crud/payments/create' );
        $I->seeResponseCodeIs( 200 );
        $I->seeInSource( '<form' );
        $I->seeInSource( 'name="amount"' );
        $I->seeInSource( 'name="currency"' );
    }


    // ── Store ─────────────────────────────────────────────────────────────────

    public function storeCreatesEntityAndRedirectsToList( FunctionalTester $I ): void
    {
        $I->sendPost( '/crud/payments/create', [
            'amount'   => '99.99',
            'currency' => 'EUR',
            'status'   => 'pending',
        ] );

        $I->seeResponseCodeIsRedirection();
        $I->followRedirect();
        $I->seeCurrentUrlContains( '/crud/payments' );

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

    public function storeInvalidDataRedirectsBackWithErrors( FunctionalTester $I ): void
    {
        $I->amOnPage( '/crud/payments/create' );
        $I->seeResponseCodeIs( 200 );

        $I->submitForm( 'form', [
            'amount'   => '-5',
            'currency' => 'TOOLONG',
            'status'   => 'pending',
        ] );

        $I->seeResponseCodeIsRedirection();
        $I->followRedirect();
        $I->seeInSource( 'alert-danger' );
    }


    // ── Edit form ─────────────────────────────────────────────────────────────

    public function editFormLoadsWithEntityData( FunctionalTester $I ): void
    {
        $I->amOnPage( '/crud/payments/' . $this->entityId . '/edit' );
        $I->seeResponseCodeIs( 200 );
        $I->seeInSource( '<form' );
        $I->seeInSource( '10.00' );
    }

    public function editNonExistentEntityRedirects( FunctionalTester $I ): void
    {
        $I->amOnPage( '/crud/payments/999999/edit' );
        $I->seeResponseCodeIsRedirection();
    }


    // ── Update ───────────────────────────────────────────────────────────────

    public function updateSavesChangesAndRedirectsToList( FunctionalTester $I ): void
    {
        $I->sendPost( '/crud/payments/' . $this->entityId . '/edit', [
            'amount'   => '149.99',
            'currency' => 'USD',
            'status'   => 'completed',
        ] );

        $I->seeResponseCodeIsRedirection();
        $I->followRedirect();
        $I->seeCurrentUrlContains( '/crud/payments' );

        $this->em->clear();
        $updated = $this->em->find( Payment::class, $this->entityId );
        $I->assertEquals( 'completed', $updated->getStatus() );
    }

    public function updateInvalidDataRedirectsBackWithErrors( FunctionalTester $I ): void
    {
        $I->amOnPage( '/crud/payments/' . $this->entityId . '/edit' );
        $I->seeResponseCodeIs( 200 );

        $I->submitForm( 'form', [
            'amount'   => '-1',
            'currency' => 'USD',
            'status'   => 'pending',
        ] );

        $I->seeResponseCodeIsRedirection();
        $I->followRedirect();
        $I->seeInSource( 'alert-danger' );
    }


    // ── Delete ────────────────────────────────────────────────────────────────

    public function deleteRemovesEntityAndRedirectsToList( FunctionalTester $I ): void
    {
        $I->sendPost( '/crud/payments/' . $this->entityId . '/delete' );
        $I->seeResponseCodeIsRedirection();
        $I->followRedirect();
        $I->seeCurrentUrlContains( '/crud/payments' );

        $this->em->clear();
        $I->assertNull( $this->em->find( Payment::class, $this->entityId ) );
    }

    public function deleteNonExistentEntityRedirects( FunctionalTester $I ): void
    {
        $I->sendPost( '/crud/payments/999999/delete' );
        $I->seeResponseCodeIsRedirection();
    }

}
