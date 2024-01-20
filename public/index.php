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

use App\Entity\User;
use App\Kernel;
use PHP_SF\Framework\Http\Middleware\auth;
use PHP_SF\System as PHP_SF;
use PHP_SF\System\Router;
use PHP_SF\Templates\Layout\footer;
use PHP_SF\Templates\Layout\header;
use Symfony\Component\Dotenv\Dotenv;
use Symfony\Component\ErrorHandler\Debug;

/**
 * By default uopz disables the exit opcode, so exit() calls are
 * practically ignored. uopz_allow_exit() allows to control this behavior.
 *
 * @url https://www.php.net/manual/en/function.uopz-allow-exit
 */
if ( function_exists( 'uopz_allow_exit' ) )
    /** @noinspection PhpUndefinedFunctionInspection */
    uopz_allow_exit( /* Whether to allow the execution of exit opcodes or not.  */ true );

define( 'start_time', microtime( true ) );

require_once __DIR__ . '/../Platform/vendor/autoload.php';
require_once __DIR__ . '/../functions/functions.php';
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../vendor/autoload.php';

if ( DEV_MODE )
    Debug::enable();

require_once __DIR__ . '/../config/eventListeners.php';

( new Dotenv )->bootEnv( __DIR__ . '/../.env' );

$kernel = ( new PHP_SF\Kernel )
    ->addTranslationFiles( __DIR__ . '/../lang' )
    ->addControllers( __DIR__ . '/../App/Http/Controller' )
    ->setHeaderTemplateClassName( header::class )
    ->setFooterTemplateClassName( footer::class )
    ->setApplicationUserClassName( User::class )
    ->addTemplatesDirectory( 'templates', 'App\View' );

require_once __DIR__ . '/testing.php';

auth::logInUser();
Router::init( $kernel );

Kernel::addRoutesToSymfony();

require_once __DIR__ . '/../autoload_runtime.php';
