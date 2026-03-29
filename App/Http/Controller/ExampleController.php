<?php /** @noinspection PhpUnused */
declare( strict_types=1 );

namespace App\Http\Controller;

use App\Http\Middleware\blank;
use App\View\welcome_page;
use Composer\InstalledVersions;
use PHP_SF\Framework\Http\Middleware\admin_example;
use PHP_SF\Framework\Http\Middleware\api_example;
use PHP_SF\Framework\Http\Middleware\auth;
use PHP_SF\System\Attributes\Route;
use PHP_SF\System\Classes\Abstracts\AbstractController;
use PHP_SF\System\Classes\MiddlewareChecks\MiddlewareAny as any;
use PHP_SF\System\Classes\MiddlewareChecks\MiddlewareCustom as custom;
use PHP_SF\System\Core\RedirectResponse;
use PHP_SF\System\Core\Response;
use PHP_SF\System\Kernel;
use PHP_SF\Templates\Auth\login_page;
use Symfony\Component\HttpFoundation\JsonResponse;

final class ExampleController extends AbstractController
{

    #[Route( url: '/', httpMethod: 'GET', middleware: [ blank::class ] )]
    public function welcome_page(): Response
    {
        return $this->render( welcome_page::class, [
            'framework_version' => InstalledVersions::getPrettyVersion( 'nations-original/php-simple-framework' ),
        ] );
    }

    #[Route( url: 'example/page/{response_type}', httpMethod: 'GET', middleware: [ custom::class => [ any::class => [ auth::class, api_example::class, admin_example::class ] ] ] )]
    public function example_route( string $response_type ): Response|RedirectResponse|JsonResponse
    {
        // Return Response
        if ( $response_type === 'response' )
            return $this->render( login_page::class, [
                'user' => ( Kernel::getApplicationUserClassName() )::find( 1 ),
            ] );

        // Returns RedirectResponse
        if ( $response_type === 'redirect_response' )
            return $this->redirectTo( 'example_route', withParams: [
                'response_type' => 'response',
            ] );

        // if ( $responseType === 'json_response' )
        return new JsonResponse( [ 'status' => 'ok' ] );
    }

}
