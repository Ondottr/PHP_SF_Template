<?php declare( strict_types=1 );

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

use PHP_SF\Framework\Http\Middleware\auth;
use PHP_SF\System as PHP_SF;
use PHP_SF\System\Router;
use PHP_SF\Templates\Layout\footer;
use PHP_SF\Templates\Layout\header;
use Symfony\Component\Dotenv\Dotenv;
use App\Entity\Main\User;


require_once __DIR__ . '/../Platform/vendor/autoload.php';
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
    ->setApplicationUserClassName( User::class )
;

Router::loadRoutesOnly( $kernel );

auth::logInUser();

/** @noinspection GlobalVariableUsageInspection */
$GLOBALS['kernel'] = $kernel;
