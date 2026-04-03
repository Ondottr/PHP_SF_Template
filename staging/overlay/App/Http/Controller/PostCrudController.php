<?php /** @noinspection AutowireWrongClass */
/** @noinspection PhpUnused */
declare( strict_types=1 );

namespace App\Http\Controller;

use App\Entity\Blog\Post;
use App\Http\Middleware\crud;
use App\Repository\Blog\PostRepository;
use App\View\Crud\Post\post_form;
use App\View\Crud\Post\post_list;
use DateTime;
use PHP_SF\System\Attributes\Route;
use PHP_SF\System\Classes\Abstracts\AbstractController;
use PHP_SF\System\Core\RedirectResponse;
use PHP_SF\System\Core\Response;
use Symfony\Component\HttpFoundation\Request;

final class PostCrudController extends AbstractController
{

    private readonly PostRepository $postRepository;


    public function __construct(
        protected Request|null $request,
    ) {
        parent::__construct( $request );

        /** @noinspection PhpFieldAssignmentTypeMismatchInspection */
        $this->postRepository = Post::rep();
    }


    #[Route( url: 'crud/posts', httpMethod: 'GET', name: 'crud_post_list', middleware: [ crud::class ] )]
    public function list(): Response
    {
        return $this->render( post_list::class, [
            'posts' => Post::findAll(),
        ], pageTitle: 'Posts List' );
    }

    #[Route( url: 'crud/posts/create', httpMethod: 'GET', name: 'crud_post_create', middleware: [ crud::class ] )]
    public function create(): Response
    {
        return $this->render( post_form::class, [
            'post' => null,
        ], pageTitle: 'Create Post' );
    }

    #[Route( url: 'crud/posts/create', httpMethod: 'POST', name: 'crud_post_store', middleware: [ crud::class ] )]
    public function store(): RedirectResponse
    {
        $post = new Post();
        $this->fill( $post );

        if ( $post->validate() === false ) {
            return $this->redirectBack( errors: array_values( $post->getValidationErrors() ) );
        }

        $this->postRepository->persist( $post );

        return $this->redirectTo( 'crud_post_list', messages: [ RedirectResponse::ALERT_SUCCESS => 'Post created successfully.' ] );
    }

    #[Route( url: 'crud/posts/{id}/edit', httpMethod: 'GET', name: 'crud_post_edit', middleware: [ crud::class ] )]
    public function edit( int $id ): Response|RedirectResponse
    {
        $post = Post::find( $id );
        if ( $post === null ) {
            return $this->redirectTo( 'crud_post_list', errors: [ RedirectResponse::ALERT_DANGER => 'Post not found.' ] );
        }

        return $this->render( post_form::class, [
            'post' => $post,
        ], pageTitle: 'Edit Post' );
    }

    #[Route( url: 'crud/posts/{id}/edit', httpMethod: 'POST', name: 'crud_post_update', middleware: [ crud::class ] )]
    public function update( int $id ): RedirectResponse
    {
        $post = Post::find( $id );
        if ( $post === null ) {
            return $this->redirectTo( 'crud_post_list', errors: [ RedirectResponse::ALERT_DANGER => 'Post not found.' ] );
        }

        $this->fill( $post );

        if ( $post->validate() === false ) {
            return $this->redirectBack( errors: array_values( $post->getValidationErrors() ) );
        }

        $this->postRepository->persist( $post );

        return $this->redirectTo( 'crud_post_list', messages: [ RedirectResponse::ALERT_SUCCESS => 'Post updated successfully.' ] );
    }

    #[Route( url: 'crud/posts/{id}/delete', httpMethod: 'POST', name: 'crud_post_delete', middleware: [ crud::class ] )]
    public function delete( int $id ): RedirectResponse
    {
        $post = Post::find( $id );
        if ( $post === null ) {
            return $this->redirectTo( 'crud_post_list', errors: [ RedirectResponse::ALERT_DANGER => 'Post not found.' ] );
        }

        $this->postRepository->remove( $post );

        return $this->redirectTo( 'crud_post_list', messages: [ RedirectResponse::ALERT_SUCCESS => 'Post deleted successfully.' ] );
    }


    private function fill( Post $post ): void
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

        $post->setTitle( $r->get( 'title', '' ) )
            ->setContent( $r->get( 'content' ) ?: null )
            ->setStatus( $r->get( 'status', 'draft' ) )
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
