<?php /** @noinspection AutowireWrongClass */
/** @noinspection PhpUnused */
declare( strict_types=1 );

namespace App\Http\Controller;

use App\Entity\Main\User;
use App\Http\Middleware\crud;
use App\Repository\Main\UserRepository;
use App\View\Crud\User\user_form;
use App\View\Crud\User\user_list;
use DateTime;
use PHP_SF\System\Attributes\Route;
use PHP_SF\System\Classes\Abstracts\AbstractController;
use PHP_SF\System\Core\RedirectResponse;
use PHP_SF\System\Core\Response;
use Symfony\Component\HttpFoundation\Request;

final class UserCrudController extends AbstractController
{

    private readonly UserRepository $userRepository;


    public function __construct(
        protected Request|null $request,
    ) {
        parent::__construct( $request );

        /** @noinspection PhpFieldAssignmentTypeMismatchInspection */
        $this->userRepository = User::rep();
    }


    #[Route( url: 'crud/users', httpMethod: 'GET', name: 'crud_user_list', middleware: [ crud::class ] )]
    public function list(): Response
    {
        return $this->render( user_list::class, [
            'users' => User::findAll(),
        ], pageTitle: 'User List' );
    }

    #[Route( url: 'crud/users/create', httpMethod: 'GET', name: 'crud_user_create', middleware: [ crud::class ] )]
    public function create(): Response
    {
        return $this->render( user_form::class, [
            'user' => null,
        ],
            pageTitle: 'Create User'
        );
    }

    #[Route( url: 'crud/users/create', httpMethod: 'POST', name: 'crud_user_store', middleware: [ crud::class ] )]
    public function store(): RedirectResponse
    {
        $rawPassword = $this->request->request->get( 'password', '' );
        if ( $rawPassword === '' ) {
            return $this->redirectBack( errors: [ RedirectResponse::ALERT_DANGER => 'Password is required.' ] );
        }

        $user = new User();
        $user->setPassword( $rawPassword );
        $this->fill( $user );

        if ( $user->validate() === false ) {
            return $this->redirectBack( errors: array_values( $user->getValidationErrors() ) );
        }

        $this->userRepository->persist( $user );

        return $this->redirectTo( 'crud_user_list', messages: [ RedirectResponse::ALERT_SUCCESS => 'User created successfully.' ] );
    }

    #[Route( url: 'crud/users/{id}/edit', httpMethod: 'GET', name: 'crud_user_edit', middleware: [ crud::class ] )]
    public function edit( int $id ): Response|RedirectResponse
    {
        $user = User::find( $id );
        if ( $user === null ) {
            return $this->redirectTo( 'crud_user_list', errors: [ RedirectResponse::ALERT_DANGER => 'User not found.' ] );
        }

        return $this->render( user_form::class, [
            'user' => $user,
        ], pageTitle: 'Edit User' );
    }

    #[Route( url: 'crud/users/{id}/edit', httpMethod: 'POST', name: 'crud_user_update', middleware: [ crud::class ] )]
    public function update( int $id ): RedirectResponse
    {
        $user = User::find( $id );
        if ( $user === null ) {
            return $this->redirectTo( 'crud_user_list', errors: [ RedirectResponse::ALERT_DANGER => 'User not found.' ] );
        }

        $rawPassword = $this->request->request->get( 'password', '' );
        if ( $rawPassword !== '' ) {
            $user->setPassword( $rawPassword );
        }

        $this->fill( $user );

        if ( $user->validate() === false ) {
            return $this->redirectBack( errors: array_values( $user->getValidationErrors() ) );
        }

        $this->userRepository->persist( $user );

        return $this->redirectTo( 'crud_user_list', messages: [ RedirectResponse::ALERT_SUCCESS => 'User updated successfully.' ] );
    }

    #[Route( url: 'crud/users/{id}/delete', httpMethod: 'POST', name: 'crud_user_delete', middleware: [ crud::class ] )]
    public function delete( int $id ): RedirectResponse
    {
        $user = User::find( $id );
        if ( $user === null ) {
            return $this->redirectTo( 'crud_user_list', errors: [ RedirectResponse::ALERT_DANGER => 'User not found.' ] );
        }

        $this->userRepository->remove( $user );

        return $this->redirectTo( 'crud_user_list', messages: [ RedirectResponse::ALERT_SUCCESS => 'User deleted successfully.' ] );
    }


    private function fill( User $user ): void
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
            ->setColTime( ( $v = $r->get( 'colTime' ) ) ? new DateTime( '1970-01-01 ' . $v ) : null )
            ->setColJson( $json( 'colJson' ) )
            ->setColGuid( $str( 'colGuid' ) )
            ->setColArray( $json( 'colArray' ) )
            ->setColSimpleArray( $csv( 'colSimpleArray' ) );
    }

}
