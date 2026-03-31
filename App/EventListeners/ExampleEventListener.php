<?php declare( strict_types=1 );

namespace App\EventListeners;

use PHP_SF\Framework\Http\Middleware\api_example;
use PHP_SF\System\Classes\Abstracts\AbstractController;
use PHP_SF\System\Classes\Abstracts\AbstractEventListener;
use PHP_SF\System\Classes\Abstracts\Middleware;
use Symfony\Component\HttpFoundation\Request;

final class ExampleEventListener extends AbstractEventListener
{

    public function getListeners(): array
    {
        return [
            // Method “listener” will be executed after loading the “auth” middleware
            api_example::class => 'listener',
        ];
    }


    private function listener( AbstractController $controller, Middleware $middleware, Request $request ): void
    {
//        dump( $controller, $middleware, $request );
//
//        trigger_error( 'This is an example of an event listener', E_USER_NOTICE );
    }

}
