<?php declare( strict_types=1 );
/*
 * Copyright © 2018-2024, Nations Original Sp. z o.o. <contact@nations-original.com>
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

namespace App;

use OpenApi\Attributes\Response;
use PHP_SF\System\Router;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;
use Symfony\Component\Routing\Route;

final class Kernel extends BaseKernel
{
    use MicroKernelTrait;

    private static bool $isEditorActivated = false;

    private static self $instance;


    public function __construct( string $environment, bool $debug )
    {
        parent::__construct( $environment, $debug );
    }


    public static function getInstance(): self
    {
        if ( !isset( self::$instance ) )
            self::setInstance();

        return self::$instance;
    }

    private static function setInstance(): void
    {
        self::$instance = new self(
            env( 'APP_ENV' ), env( 'APP_DEBUG' ) === 'true' || env( 'APP_DEBUG' ) === '1'
        );

        self::$instance->boot();
    }

    public static function isEditorActivated(): bool
    {
        return self::$isEditorActivated;
    }

    public static function setEditorStatus( bool $isEditorActivated ): void
    {
        self::$isEditorActivated = $isEditorActivated;
    }

    public static function addRoutesToSymfony(): void
    {
        $collection = self::getInstance()
            ->getContainer()
            ->get( 'router' )
            ->getRouteCollection();

        foreach ( Router::getRoutesList() as $routeName => $route ) {
            if ( DEV_MODE || ( $OAResponseAttrs = mca()->get( "cache:oa_response_attrs:$routeName" ) ) === null ) {
                $OAResponseAttrs = ( new ReflectionClass( $route['class'] ) )
                    ->getMethod( $route['method'] )
                    ->getAttributes( Response::class );

                $OAResponseAttrs = !empty( $OAResponseAttrs );

                if ( DEV_MODE === false )
                    mca()->set( "cache:oa_response_attrs:$routeName", $OAResponseAttrs );

            }

            if ( $OAResponseAttrs === false )
                continue;


            $route['url'] = str_replace( '{$', '/{', $route['url'] );
            $route = ( new Route( $route['url'] ) )
                ->setMethods( [ $route['httpMethod'] ] )
                ->addDefaults( [ '_controller' => $route['class'] . '::' . $route['method'] ] );

            $collection->add( $routeName, $route );
        }
    }

}
