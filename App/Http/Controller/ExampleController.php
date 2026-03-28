<?php /** @noinspection PhpUnused */
declare( strict_types=1 );
/*
 * Copyright © 2018-2026, Nations Original Sp. z o.o. <contact@nations-original.com>
 *
 * Permission to use, copy, modify, and/or distribute this software for any purpose with or without fee is hereby
 * granted, provided that the above copyright notice and this permission notice appear in all copies.
 *
 * THE SOFTWARE IS PROVIDED \"AS IS\" AND THE AUTHOR DISCLAIMS ALL WARRANTIES WITH REGARD TO THIS SOFTWARE
 * INCLUDING ALL IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS. IN NO EVENT SHALL THE AUTHOR BE
 * LIABLE FOR ANY SPECIAL, DIRECT, INDIRECT, OR CONSEQUENTIAL DAMAGES OR ANY DAMAGES WHATSOEVER
 * RESULTING FROM LOSS OF USE, DATA OR PROFITS, WHETHER IN AN ACTION OF CONTRACT, NEGLIGENCE OR OTHER
 * TORTIOUS ACTION, ARISING OUT OF OR IN CONNECTION WITH THE USE OR PERFORMANCE OF THIS SOFTWARE.
 */

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
