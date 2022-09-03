<?php declare(strict_types=1);
/*
 * Copyright © 2018-2022, Nations Original Sp. z o.o. <contact@nations-original.com>
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

use App\Helpers\Controllers\EntityRepositoriesTrait;
use App\Http\Middleware\example_middleware;
use PHP_SF\Framework\Http\Middleware\cron;
use PHP_SF\System\Attributes\Route;
use PHP_SF\System\Classes\Abstracts\AbstractController;
use PHP_SF\System\Core\RedirectResponse;
use PHP_SF\System\Core\Response;
use PHP_SF\Templates\Auth\login_page;
use Symfony\Component\HttpFoundation\JsonResponse;

final class ExampleController extends AbstractController
{
    use EntityRepositoriesTrait;


    #[Route( url: 'example/page/{$response_type}', httpMethod: 'GET', middleware: [ cron::class, example_middleware::class ] )]
    public function example_route( string $response_type ): Response|RedirectResponse|JsonResponse
    {
        // Return Response
        if ( $response_type === 'response' )
            return $this->render( login_page::class, [
                'user' => $this->userRepository( true )->find( 1 ),
            ] );

        // Returns RedirectResponse
        if ( $response_type === 'redirect_response' )
            return $this->redirectTo('example_route', withParams: [
                'response_type' => 'response',
            ]);

       // if ( $responseType === 'json_response' )
            return new JsonResponse(
                status: JsonResponse::HTTP_NO_CONTENT
            );
    }

}
