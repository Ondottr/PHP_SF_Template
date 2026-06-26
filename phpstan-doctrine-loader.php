<?php declare(strict_types=1);

// Provides the default Doctrine object manager for phpstan/phpstan-doctrine.
// Uses the 'main' entity manager — the primary PostgreSQL EM for most entities.

use Symfony\Component\Dotenv\Dotenv;

require_once __DIR__ . '/vendor/autoload.php';

(new Dotenv())->bootEnv(__DIR__ . '/.env');

$kernel = new App\Kernel('dev', true);
$kernel->boot();

return $kernel->getContainer()->get('doctrine.orm.main_entity_manager');
