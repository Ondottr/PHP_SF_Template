<?php declare( strict_types=1 );

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

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../functions/functions.php';
require_once __DIR__ . '/../config/constants.php';

if ( DEV_MODE )
    Debug::enable();

require_once __DIR__ . '/../config/eventListeners.php';

( new Dotenv() )->bootEnv( __DIR__ . '/../.env' );

$kernel = ( new PHP_SF\Kernel() )
    ->addTranslationFiles( __DIR__ . '/../lang' )
    ->addControllers( __DIR__ . '/../App/Http/Controller' )
    ->setHeaderTemplateClassName( header::class )
    ->setFooterTemplateClassName( footer::class )
    ->addTemplatesDirectory( 'templates', 'App\View' )
;

require_once __DIR__ . '/testing.php';

auth::logInUser();
Router::init( $kernel );

Kernel::addRoutesToSymfony();

require_once __DIR__ . '/../autoload_runtime.php';
