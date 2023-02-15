<?php declare( strict_types=1 );
/*
 * Copyright © 2018-2023, Nations Original Sp. z o.o. <contact@nations-original.com>
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

use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

use function dirname;

final class Kernel extends BaseKernel
{
    use MicroKernelTrait;

    private static bool $isEditorActivated = false;

    private static self $instance;


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

    protected function configureContainer( ContainerConfigurator $container ): void
    {
        $container->import( '../config/{packages}/*.yaml' );
        $container->import( '../config/{packages}/' . $this->environment . '/*.yaml' );

        if ( is_file( dirname( __DIR__ ) . '/config/services.yaml' ) ) {
            $container->import( '../config/services.yaml' );
            $container->import( '../config/{services}_' . $this->environment . '.yaml' );
        } else
            $container->import( '../config/{services}.php' );

    }

    protected function configureRoutes( RoutingConfigurator $routes ): void
    {
        $routes->import( '../config/{routes}/' . $this->environment . '/*.yaml' );
        $routes->import( '../config/{routes}/*.yaml' );

        if ( is_file( dirname( __DIR__ ) . '/config/routes.yaml' ) )
            $routes->import( '../config/routes.yaml' );
        else
            $routes->import( '../config/{routes}.php' );

    }

}
