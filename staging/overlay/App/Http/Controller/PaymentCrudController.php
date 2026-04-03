<?php /** @noinspection AutowireWrongClass */
/** @noinspection PhpUnused */
declare( strict_types=1 );

namespace App\Http\Controller;

use App\Entity\Payments\Payment;
use App\Http\Middleware\crud;
use App\Repository\Payments\PaymentRepository;
use App\View\Crud\Payment\payment_form;
use App\View\Crud\Payment\payment_list;
use DateTime;
use PHP_SF\System\Attributes\Route;
use PHP_SF\System\Classes\Abstracts\AbstractController;
use PHP_SF\System\Core\RedirectResponse;
use PHP_SF\System\Core\Response;
use Symfony\Component\HttpFoundation\Request;

final class PaymentCrudController extends AbstractController
{

    private readonly PaymentRepository $paymentRepository;


    public function __construct(
        protected Request|null $request,
    ) {
        parent::__construct( $request );

        /** @noinspection PhpFieldAssignmentTypeMismatchInspection */
        $this->paymentRepository = Payment::rep();
    }


    #[Route( url: 'crud/payments', httpMethod: 'GET', name: 'crud_payment_list', middleware: [ crud::class ] )]
    public function list(): Response
    {
        return $this->render( payment_list::class, [
            'payments' => Payment::findAll(),
        ], pageTitle: 'Payments List' );
    }

    #[Route( url: 'crud/payments/create', httpMethod: 'GET', name: 'crud_payment_create', middleware: [ crud::class ] )]
    public function create(): Response
    {
        return $this->render( payment_form::class, [
            'payment' => null,
        ], pageTitle: 'Create Payment' );
    }

    #[Route( url: 'crud/payments/create', httpMethod: 'POST', name: 'crud_payment_store', middleware: [ crud::class ] )]
    public function store(): RedirectResponse
    {
        $payment = new Payment();
        $this->fill( $payment );

        if ( $payment->validate() === false ) {
            return $this->redirectBack( errors: array_values( $payment->getValidationErrors() ) );
        }

        $this->paymentRepository->persist( $payment );

        return $this->redirectTo( 'crud_payment_list', messages: [ RedirectResponse::ALERT_SUCCESS => 'Payment created successfully.' ] );
    }

    #[Route( url: 'crud/payments/{id}/edit', httpMethod: 'GET', name: 'crud_payment_edit', middleware: [ crud::class ] )]
    public function edit( int $id ): Response|RedirectResponse
    {
        $payment = Payment::find( $id );
        if ( $payment === null ) {
            return $this->redirectTo( 'crud_payment_list', errors: [ RedirectResponse::ALERT_DANGER => 'Payment not found.' ] );
        }

        return $this->render( payment_form::class, [
            'payment' => $payment,
        ], pageTitle: 'Edit Payment' );
    }

    #[Route( url: 'crud/payments/{id}/edit', httpMethod: 'POST', name: 'crud_payment_update', middleware: [ crud::class ] )]
    public function update( int $id ): RedirectResponse
    {
        $payment = Payment::find( $id );
        if ( $payment === null ) {
            return $this->redirectTo( 'crud_payment_list', errors: [ RedirectResponse::ALERT_DANGER => 'Payment not found.' ] );
        }

        $this->fill( $payment );

        if ( $payment->validate() === false ) {
            return $this->redirectBack( errors: array_values( $payment->getValidationErrors() ) );
        }

        $this->paymentRepository->persist( $payment );

        return $this->redirectTo( 'crud_payment_list', messages: [ RedirectResponse::ALERT_SUCCESS => 'Payment updated successfully.' ] );
    }

    #[Route( url: 'crud/payments/{id}/delete', httpMethod: 'POST', name: 'crud_payment_delete', middleware: [ crud::class ] )]
    public function delete( int $id ): RedirectResponse
    {
        $payment = Payment::find( $id );
        if ( $payment === null ) {
            return $this->redirectTo( 'crud_payment_list', errors: [ RedirectResponse::ALERT_DANGER => 'Payment not found.' ] );
        }

        $this->paymentRepository->remove( $payment );

        return $this->redirectTo( 'crud_payment_list', messages: [ RedirectResponse::ALERT_SUCCESS => 'Payment deleted successfully.' ] );
    }


    private function fill( Payment $payment ): void
    {
        $r    = $this->request->request;
        $str  = static fn( string $k ) => ( $v = $r->get( $k ) ) !== '' && $v !== null ? $v : null;
        $int  = static fn( string $k ) => ( $v = $r->get( $k ) ) !== '' && $v !== null ? (int)$v : null;
        $flt  = static fn( string $k ) => ( $v = $r->get( $k ) ) !== '' && $v !== null ? (float)$v : null;
        $bool = static fn( string $k ) => match ( $r->get( $k ) ) {
            '1'     => true,
            '0'     => false,
            default => null
        };
        $dt   = static fn( string $k ) => ( $v = $r->get( $k ) ) ? new DateTime( $v ) : null;
        $json = static fn( string $k ) => ( $v = $r->get( $k ) ) !== '' && $v !== null ? ( json_decode( $v, true ) ?: null ) : null;
        $csv  = static fn( string $k ) => ( $v = $r->get( $k ) ) !== '' && $v !== null
            ? array_values( array_filter( array_map( 'trim', explode( ',', $v ) ) ) )
            : null;

        $payment->setAmount( $str( 'amount' ) )
            ->setCurrency( strtoupper( $r->get( 'currency', 'USD' ) ) )
            ->setStatus( $r->get( 'status', 'pending' ) )
            ->setColText( $str( 'colText' ) )
            ->setColInteger( $int( 'colInteger' ) )
            ->setColSmallint( $int( 'colSmallint' ) )
            ->setColBigint( $str( 'colBigint' ) )
            ->setColBoolean( $bool( 'colBoolean' ) )
            ->setColDecimal( $str( 'colDecimal' ) )
            ->setColFloat( $flt( 'colFloat' ) )
            ->setColDate( $dt( 'colDate' ) )
            ->setColTime( ( $v = $r->get( 'colTime' ) ) ? new DateTime( '1970-01-01 ' . $v ) : null )
            ->setColJson( $json( 'colJson' ) )
            ->setColGuid( $str( 'colGuid' ) )
            ->setColArray( $json( 'colArray' ) )
            ->setColSimpleArray( $csv( 'colSimpleArray' ) );
    }

}
