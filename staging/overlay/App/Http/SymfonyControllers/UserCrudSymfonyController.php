<?php /** @noinspection PhpUnused */
declare( strict_types=1 );

namespace App\Http\SymfonyControllers;

use App\Entity\Main\User;
use App\Repository\Main\UserRepository;
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

final class UserCrudSymfonyController
{

    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly Environment $twig,
        private readonly CsrfTokenManagerInterface $csrfTokenManager,
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly RequestStack $requestStack
    ) {}

    /** @return string[] */
    private function fill( User $user, Request $request ): array
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

        $user->setEmail( $r->get( 'email' ) )
            ->setColText( $str( 'colText' ) )
            ->setColInteger( $int( 'colInteger' ) )
            ->setColSmallint( $int( 'colSmallint' ) )
            ->setColBigint( $str( 'colBigint' ) )
            ->setColBoolean( $bool( 'colBoolean' ) )
            ->setColDecimal( $str( 'colDecimal' ) )
            ->setColFloat( $flt( 'colFloat' ) )
            ->setColDatetimetz( $dt( 'colDatetimetz' ) )
            ->setColDate( $dt( 'colDate' ) )
            ->setColTime( $colTime )
            ->setColJson( $json( 'colJson' ) )
            ->setColGuid( $str( 'colGuid' ) )
            ->setColArray( $json( 'colArray' ) )
            ->setColSimpleArray( $csv( 'colSimpleArray' ) );

        return $parseErrors;
    }


    #[Route( '/symfony/crud/users', name: 'symfony_crud_user_list', methods: [ 'GET' ] )]
    public function list(): Response
    {
        return new Response( $this->twig->render( 'crud/users/index.html.twig', [
            'users' => $this->userRepository->findAll(),
        ] ) );
    }

    #[Route( '/symfony/crud/users/create', name: 'symfony_crud_user_create', methods: [ 'GET' ] )]
    public function create(): Response
    {
        return new Response( $this->twig->render( 'crud/users/form.html.twig', [
            'user'      => null,
            'errors'    => [],
            'form_data' => [],
        ] ) );
    }

    #[Route( '/symfony/crud/users/create', name: 'symfony_crud_user_store', methods: [ 'POST' ] )]
    public function store( Request $request ): Response
    {
        if ( !$this->csrfTokenManager->isTokenValid( new CsrfToken( 'submit', $request->request->get( '_token', '' ) ) ) ) {
            return new Response( $this->twig->render( 'crud/users/form.html.twig', [
                'user'      => null,
                'errors'    => [ 'Invalid CSRF token.' ],
                'form_data' => $request->request->all(),
            ] ) );
        }

        $rawPassword = $request->request->get( 'password', '' );
        if ( $rawPassword === '' ) {
            return new Response( $this->twig->render( 'crud/users/form.html.twig', [
                'user'      => null,
                'errors'    => [ 'Password is required.' ],
                'form_data' => $request->request->all(),
            ] ) );
        }

        $user = new User();
        $user->setPassword( $rawPassword );
        $parseErrors = $this->fill( $user, $request );

        if ( $parseErrors !== [] || $user->validate() === false ) {
            return new Response( $this->twig->render( 'crud/users/form.html.twig', [
                'user'      => null,
                'errors'    => array_values( array_merge( $parseErrors, $user->getValidationErrors() ) ),
                'form_data' => $request->request->all(),
            ] ) );
        }

        $this->userRepository->persist( $user );
        $this->requestStack->getSession()->getFlashBag()->add( 'success', 'User created successfully.' );

        return new RedirectResponse( $this->urlGenerator->generate( 'symfony_crud_user_list' ) );
    }

    #[Route( '/symfony/crud/users/{id}/edit', name: 'symfony_crud_user_edit', methods: [ 'GET' ] )]
    public function edit( int $id ): Response
    {
        $user = $this->userRepository->find( $id );
        if ( $user === null ) {
            $this->requestStack->getSession()->getFlashBag()->add( 'danger', 'User not found.' );
            return new RedirectResponse( $this->urlGenerator->generate( 'symfony_crud_user_list' ) );
        }

        return new Response( $this->twig->render( 'crud/users/form.html.twig', [
            'user'      => $user,
            'errors'    => [],
            'form_data' => [],
        ] ) );
    }

    #[Route( '/symfony/crud/users/{id}/edit', name: 'symfony_crud_user_update', methods: [ 'POST' ] )]
    public function update( int $id, Request $request ): Response
    {
        $user = $this->userRepository->find( $id );
        if ( $user === null ) {
            $this->requestStack->getSession()->getFlashBag()->add( 'danger', 'User not found.' );
            return new RedirectResponse( $this->urlGenerator->generate( 'symfony_crud_user_list' ) );
        }

        if ( !$this->csrfTokenManager->isTokenValid( new CsrfToken( 'submit', $request->request->get( '_token', '' ) ) ) ) {
            return new Response( $this->twig->render( 'crud/users/form.html.twig', [
                'user'      => $user,
                'errors'    => [ 'Invalid CSRF token.' ],
                'form_data' => $request->request->all(),
            ] ) );
        }

        $rawPassword = $request->request->get( 'password', '' );
        if ( $rawPassword !== '' ) {
            $user->setPassword( $rawPassword );
        }

        $parseErrors = $this->fill( $user, $request );

        if ( $parseErrors !== [] || $user->validate() === false ) {
            return new Response( $this->twig->render( 'crud/users/form.html.twig', [
                'user'      => $user,
                'errors'    => array_values( array_merge( $parseErrors, $user->getValidationErrors() ) ),
                'form_data' => $request->request->all(),
            ] ) );
        }

        $this->userRepository->persist( $user );
        $this->requestStack->getSession()->getFlashBag()->add( 'success', 'User updated successfully.' );

        return new RedirectResponse( $this->urlGenerator->generate( 'symfony_crud_user_list' ) );
    }

    #[Route( '/symfony/crud/users/{id}/delete', name: 'symfony_crud_user_delete', methods: [ 'POST' ] )]
    public function delete( int $id, Request $request ): Response
    {
        $user = $this->userRepository->find( $id );
        if ( $user === null ) {
            $this->requestStack->getSession()->getFlashBag()->add( 'danger', 'User not found.' );
            return new RedirectResponse( $this->urlGenerator->generate( 'symfony_crud_user_list' ) );
        }

        if ( !$this->csrfTokenManager->isTokenValid( new CsrfToken( 'submit', $request->request->get( '_token', '' ) ) ) ) {
            $this->requestStack->getSession()->getFlashBag()->add( 'danger', 'Invalid CSRF token.' );
            return new RedirectResponse( $this->urlGenerator->generate( 'symfony_crud_user_list' ) );
        }

        $this->userRepository->remove( $user );
        $this->requestStack->getSession()->getFlashBag()->add( 'success', 'User deleted successfully.' );

        return new RedirectResponse( $this->urlGenerator->generate( 'symfony_crud_user_list' ) );
    }

}
