<?php declare(strict_types=1);

use App\Kernel;

require_once __DIR__ . '/vendor/autoload.php';

if (empty($_SERVER['SCRIPT_FILENAME'])) {
    return;
}

$app = function (): Kernel {
    return Kernel::getInstance();
};

if (!is_object($app)) {
    throw new TypeError(
        sprintf(
            'Invalid return value: callable object expected, "%s" returned from "%s".',
            get_debug_type($app),
            $_SERVER['SCRIPT_FILENAME'],
        ),
    );
}

$runtime = $_SERVER['APP_RUNTIME'] ?? $_ENV['APP_RUNTIME'] ?? 'Symfony\\Component\\Runtime\\SymfonyRuntime';
$runtime = new $runtime(
    ($_SERVER['APP_RUNTIME_OPTIONS'] ?? $_ENV['APP_RUNTIME_OPTIONS'] ?? []) + [
        'project_dir' => __DIR__,
    ],
);

[
    $app,
    $args
] = $runtime
    ->getResolver($app)
    ->resolve();

$app = $app($args);

exit(
    $runtime
        ->getRunner($app)
        ->run()
);
