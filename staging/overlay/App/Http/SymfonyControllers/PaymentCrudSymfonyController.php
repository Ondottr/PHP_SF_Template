<?php /** @noinspection PhpUnused */
declare( strict_types=1 );

namespace App\Http\SymfonyControllers;

use App\Entity\Payments\Payment;
use App\Repository\Payments\PaymentRepository;
use DateTime;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Twig\Environment;

final class PaymentCrudSymfonyController
{

    public function __construct(
        private readonly PaymentRepository $paymentRepository,
        private readonly Environment $twig,
        private readonly CsrfTokenManagerInterface $csrfTokenManager,
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly RequestStack $requestStack
    ) {}

    /** @return string[] */
    private function fill( Payment $payment, Request $request ): array
    {
        $r           = $request->request;
        $parseErrors = [];
        $str         = static fn( string $k ) => ( $v = $r->get( $k ) ) !== '' && $v !== null ? $v : null;
        $int         = static fn( string $k ) => ( $v = $r->get( $k ) ) !== '' && $v !== null ? (int) $v : null;
        $flt         = static fn( string $k ) => ( $v = $r->get( $k ) ) !== '' && $v !== null ? (float) $v : null;
        $bool        = static fn( string $k ) => match ( $r->get( $k ) ) { '1' => true, '0' => false, default => null };
        $dt          = function( string $k ) use ( $r, &$parseErrors ): ?DateTime {
            if ( !( $v = $r->get( $k ) ) ) return null;
            try { return new DateTime( $v ); }
            catch ( \Exception ) { $parseErrors[ $k ] = sprintf( 'Invalid date/time value for "%s".', $k ); return null; }
        };
        $json = static fn( string $k ) => ( $v = $r->get( $k ) ) !== '' && $v !== null ? ( json_decode( $v, true ) ?: null ) : null;
        $csv  = static fn( string $k ) => ( $v = $r->get( $k ) ) !== '' && $v !== null
            ? array_values( array_filter( array_map( 'trim', explode( ',', $v ) ) ) ) : null;

        $colTime = null;
        if ( $v = $r->get( 'colTime' ) ) {
            try { $colTime = new DateTime( '1970-01-01 ' . $v ); }
            catch ( \Exception ) { $parseErrors['colTime'] = 'Invalid time value for "colTime".'; }
        }

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
            ->setColTime( $colTime )
            ->setColJson( $json( 'colJson' ) )
            ->setColGuid( $str( 'colGuid' ) )
            ->setColArray( $json( 'colArray' ) )
            ->setColSimpleArray( $csv( 'colSimpleArray' ) );

        return $parseErrors;
    }


    #[Route( '/symfony/crud/payments', name: 'symfony_crud_payment_list', methods: [ 'GET' ] )]
    public function list(): Response
    {
        return new Response( $this->twig->render( 'crud/payments/index.html.twig', [
            'payments' => $this->paymentRepository->findAll(),
        ] ) );
    }

    #[Route( '/symfony/crud/payments/create', name: 'symfony_crud_payment_create', methods: [ 'GET' ] )]
    public function create(): Response
    {
        return new Response( $this->twig->render( 'crud/payments/form.html.twig', [
            'payment'   => null,
            'errors'    => [],
            'form_data' => [],
        ] ) );
    }

    #[Route( '/symfony/crud/payments/create', name: 'symfony_crud_payment_store', methods: [ 'POST' ] )]
    public function store( Request $request ): Response
    {
        if ( !$this->csrfTokenManager->isTokenValid( new CsrfToken( 'submit', $request->request->get( '_token', '' ) ) ) ) {
            return new Response( $this->twig->render( 'crud/payments/form.html.twig', [
                'payment'   => null,
                'errors'    => [ 'Invalid CSRF token.' ],
                'form_data' => $request->request->all(),
            ] ) );
        }

        $payment     = new Payment();
        $parseErrors = $this->fill( $payment, $request );

        if ( $parseErrors !== [] || $payment->validate() === false ) {
            return new Response( $this->twig->render( 'crud/payments/form.html.twig', [
                'payment'   => null,
                'errors'    => array_values( array_merge( $parseErrors, $payment->getValidationErrors() ) ),
                'form_data' => $request->request->all(),
            ] ) );
        }

        $this->paymentRepository->persist( $payment );
        $this->requestStack->getSession()->getFlashBag()->add( 'success', 'Payment created successfully.' );

        return new RedirectResponse( $this->urlGenerator->generate( 'symfony_crud_payment_list' ) );
    }

    #[Route( '/symfony/crud/payments/{id}/edit', name: 'symfony_crud_payment_edit', methods: [ 'GET' ] )]
    public function edit( int $id ): Response
    {
        $payment = $this->paymentRepository->find( $id );
        if ( $payment === null ) {
            $this->requestStack->getSession()->getFlashBag()->add( 'danger', 'Payment not found.' );
            return new RedirectResponse( $this->urlGenerator->generate( 'symfony_crud_payment_list' ) );
        }

        return new Response( $this->twig->render( 'crud/payments/form.html.twig', [
            'payment'   => $payment,
            'errors'    => [],
            'form_data' => [],
        ] ) );
    }

    #[Route( '/symfony/crud/payments/{id}/edit', name: 'symfony_crud_payment_update', methods: [ 'POST' ] )]
    public function update( int $id, Request $request ): Response
    {
        $payment = $this->paymentRepository->find( $id );
        if ( $payment === null ) {
            $this->requestStack->getSession()->getFlashBag()->add( 'danger', 'Payment not found.' );
            return new RedirectResponse( $this->urlGenerator->generate( 'symfony_crud_payment_list' ) );
        }

        if ( !$this->csrfTokenManager->isTokenValid( new CsrfToken( 'submit', $request->request->get( '_token', '' ) ) ) ) {
            return new Response( $this->twig->render( 'crud/payments/form.html.twig', [
                'payment'   => $payment,
                'errors'    => [ 'Invalid CSRF token.' ],
                'form_data' => $request->request->all(),
            ] ) );
        }

        $parseErrors = $this->fill( $payment, $request );

        if ( $parseErrors !== [] || $payment->validate() === false ) {
            return new Response( $this->twig->render( 'crud/payments/form.html.twig', [
                'payment'   => $payment,
                'errors'    => array_values( array_merge( $parseErrors, $payment->getValidationErrors() ) ),
                'form_data' => $request->request->all(),
            ] ) );
        }

        $this->paymentRepository->persist( $payment );
        $this->requestStack->getSession()->getFlashBag()->add( 'success', 'Payment updated successfully.' );

        return new RedirectResponse( $this->urlGenerator->generate( 'symfony_crud_payment_list' ) );
    }

    #[Route( '/symfony/crud/payments/{id}/delete', name: 'symfony_crud_payment_delete', methods: [ 'POST' ] )]
    public function delete( int $id, Request $request ): Response
    {
        $payment = $this->paymentRepository->find( $id );
        if ( $payment === null ) {
            $this->requestStack->getSession()->getFlashBag()->add( 'danger', 'Payment not found.' );
            return new RedirectResponse( $this->urlGenerator->generate( 'symfony_crud_payment_list' ) );
        }

        if ( !$this->csrfTokenManager->isTokenValid( new CsrfToken( 'submit', $request->request->get( '_token', '' ) ) ) ) {
            $this->requestStack->getSession()->getFlashBag()->add( 'danger', 'Invalid CSRF token.' );
            return new RedirectResponse( $this->urlGenerator->generate( 'symfony_crud_payment_list' ) );
        }

        $this->paymentRepository->remove( $payment );
        $this->requestStack->getSession()->getFlashBag()->add( 'success', 'Payment deleted successfully.' );

        return new RedirectResponse( $this->urlGenerator->generate( 'symfony_crud_payment_list' ) );
    }

}
