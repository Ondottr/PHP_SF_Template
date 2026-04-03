<?php declare( strict_types=1 );

use PHP_SF\Framework\Http\Middleware\auth;
use PHP_SF\System as PHP_SF;
use PHP_SF\System\Router;
use PHP_SF\Templates\Layout\footer;
use PHP_SF\Templates\Layout\header;
use Symfony\Component\Dotenv\Dotenv;


defined( 'start_time' ) || define( 'start_time', microtime( true ) );

require_once __DIR__ . '/../functions/functions.php';
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/eventListeners.php';

// PHPUnit sets APP_ENV=test via phpunit.xml.dist <server> before this runs.
// Codeception does not, so we default it here so bootEnv('.env') picks up .env.test.
$_SERVER['APP_ENV'] ??= 'test';
$_ENV['APP_ENV']    ??= 'test';

// Boot from .env (not .env.test) so all base vars (Redis, DB hosts, …) are loaded
// first; bootEnv then also loads .env.test because APP_ENV=test.
( new Dotenv() )->bootEnv( __DIR__ . '/../.env' );

$kernel = ( new PHP_SF\Kernel() )
    ->addTranslationFiles( __DIR__ . '/../lang' )
    ->addControllers( __DIR__ . '/../App/Http/Controller' )
    ->setHeaderTemplateClassName( header::class )
    ->setFooterTemplateClassName( footer::class )
    ->addTemplatesDirectory( 'templates', 'App\View' )
;

Router::loadRoutesOnly( $kernel );

auth::logInUser();

/** @noinspection GlobalVariableUsageInspection */
$GLOBALS['kernel'] = $kernel;
