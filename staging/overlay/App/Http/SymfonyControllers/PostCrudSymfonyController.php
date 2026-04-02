<?php /** @noinspection PhpUnused */
declare( strict_types=1 );

namespace App\Http\SymfonyControllers;

use App\Entity\Blog\Post;
use App\Repository\Blog\PostRepository;
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

final class PostCrudSymfonyController
{

    public function __construct(
        private readonly PostRepository $postRepository,
        private readonly Environment $twig,
        private readonly CsrfTokenManagerInterface $csrfTokenManager,
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly RequestStack $requestStack
    ) {}

    private function fill( Post $post, Request $request ): void
    {
        $r    = $request->request;
        $str  = static fn( string $k ) => ( $v = $r->get( $k ) ) !== '' && $v !== null ? $v : null;
        $int  = static fn( string $k ) => ( $v = $r->get( $k ) ) !== '' && $v !== null ? (int) $v : null;
        $flt  = static fn( string $k ) => ( $v = $r->get( $k ) ) !== '' && $v !== null ? (float) $v : null;
        $bool = static fn( string $k ) => match ( $r->get( $k ) ) { '1' => true, '0' => false, default => null };
        $dt   = static fn( string $k ) => ( $v = $r->get( $k ) ) ? new DateTime( $v ) : null;
        $json = static fn( string $k ) => ( $v = $r->get( $k ) ) !== '' && $v !== null ? ( json_decode( $v, true ) ?: null ) : null;
        $csv  = static fn( string $k ) => ( $v = $r->get( $k ) ) !== '' && $v !== null
            ? array_values( array_filter( array_map( 'trim', explode( ',', $v ) ) ) ) : null;

        $post->setTitle( $r->get( 'title', '' ) )
             ->setContent( $str( 'content' ) )
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


    #[Route( '/symfony/crud/posts', name: 'symfony_crud_post_list', methods: [ 'GET' ] )]
    public function list(): Response
    {
        return new Response( $this->twig->render( 'crud/posts/index.html.twig', [
            'posts' => $this->postRepository->findAll(),
        ] ) );
    }

    #[Route( '/symfony/crud/posts/create', name: 'symfony_crud_post_create', methods: [ 'GET' ] )]
    public function create(): Response
    {
        return new Response( $this->twig->render( 'crud/posts/form.html.twig', [
            'post'      => null,
            'errors'    => [],
            'form_data' => [],
        ] ) );
    }

    #[Route( '/symfony/crud/posts/create', name: 'symfony_crud_post_store', methods: [ 'POST' ] )]
    public function store( Request $request ): Response
    {
        if ( !$this->csrfTokenManager->isTokenValid( new CsrfToken( 'submit', $request->request->get( '_token', '' ) ) ) ) {
            return new Response( $this->twig->render( 'crud/posts/form.html.twig', [
                'post'      => null,
                'errors'    => [ 'Invalid CSRF token.' ],
                'form_data' => $request->request->all(),
            ] ) );
        }

        $post = new Post();
        $this->fill( $post, $request );

        if ( $post->validate() === false ) {
            return new Response( $this->twig->render( 'crud/posts/form.html.twig', [
                'post'      => null,
                'errors'    => array_values( $post->getValidationErrors() ),
                'form_data' => $request->request->all(),
            ] ) );
        }

        $this->postRepository->persist( $post );
        $this->requestStack->getSession()->getFlashBag()->add( 'success', 'Post created successfully.' );

        return new RedirectResponse( $this->urlGenerator->generate( 'symfony_crud_post_list' ) );
    }

    #[Route( '/symfony/crud/posts/{id}/edit', name: 'symfony_crud_post_edit', methods: [ 'GET' ] )]
    public function edit( int $id ): Response
    {
        $post = $this->postRepository->find( $id );
        if ( $post === null ) {
            $this->requestStack->getSession()->getFlashBag()->add( 'danger', 'Post not found.' );
            return new RedirectResponse( $this->urlGenerator->generate( 'symfony_crud_post_list' ) );
        }

        return new Response( $this->twig->render( 'crud/posts/form.html.twig', [
            'post'      => $post,
            'errors'    => [],
            'form_data' => [],
        ] ) );
    }

    #[Route( '/symfony/crud/posts/{id}/edit', name: 'symfony_crud_post_update', methods: [ 'POST' ] )]
    public function update( int $id, Request $request ): Response
    {
        $post = $this->postRepository->find( $id );
        if ( $post === null ) {
            $this->requestStack->getSession()->getFlashBag()->add( 'danger', 'Post not found.' );
            return new RedirectResponse( $this->urlGenerator->generate( 'symfony_crud_post_list' ) );
        }

        if ( !$this->csrfTokenManager->isTokenValid( new CsrfToken( 'submit', $request->request->get( '_token', '' ) ) ) ) {
            return new Response( $this->twig->render( 'crud/posts/form.html.twig', [
                'post'      => $post,
                'errors'    => [ 'Invalid CSRF token.' ],
                'form_data' => $request->request->all(),
            ] ) );
        }

        $this->fill( $post, $request );

        if ( $post->validate() === false ) {
            return new Response( $this->twig->render( 'crud/posts/form.html.twig', [
                'post'      => $post,
                'errors'    => array_values( $post->getValidationErrors() ),
                'form_data' => $request->request->all(),
            ] ) );
        }

        $this->postRepository->persist( $post );
        $this->requestStack->getSession()->getFlashBag()->add( 'success', 'Post updated successfully.' );

        return new RedirectResponse( $this->urlGenerator->generate( 'symfony_crud_post_list' ) );
    }

    #[Route( '/symfony/crud/posts/{id}/delete', name: 'symfony_crud_post_delete', methods: [ 'POST' ] )]
    public function delete( int $id, Request $request ): Response
    {
        $post = $this->postRepository->find( $id );
        if ( $post === null ) {
            $this->requestStack->getSession()->getFlashBag()->add( 'danger', 'Post not found.' );
            return new RedirectResponse( $this->urlGenerator->generate( 'symfony_crud_post_list' ) );
        }

        if ( !$this->csrfTokenManager->isTokenValid( new CsrfToken( 'submit', $request->request->get( '_token', '' ) ) ) ) {
            $this->requestStack->getSession()->getFlashBag()->add( 'danger', 'Invalid CSRF token.' );
            return new RedirectResponse( $this->urlGenerator->generate( 'symfony_crud_post_list' ) );
        }

        $this->postRepository->remove( $post );
        $this->requestStack->getSession()->getFlashBag()->add( 'success', 'Post deleted successfully.' );

        return new RedirectResponse( $this->urlGenerator->generate( 'symfony_crud_post_list' ) );
    }

}
