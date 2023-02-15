<?php declare( strict_types=1 );
/*
 * Copyright © 2018-2023, Nations Original Sp. z o.o. <contact@nations-original.com>
 *
 * Permission to use, copy, modify, and/or distribute this software for any
 * purpose with or without fee is hereby granted, provided that the above
 * copyright notice and this permission notice appear in all copies.
 *
 * THE SOFTWARE IS PROVIDED \"AS IS\" AND THE AUTHOR DISCLAIMS ALL WARRANTIES
 * WITH REGARD TO THIS SOFTWARE INCLUDING ALL IMPLIED WARRANTIES OF
 * MERCHANTABILITY AND FITNESS. IN NO EVENT SHALL THE AUTHOR BE LIABLE FOR
 * ANY SPECIAL, DIRECT, INDIRECT, OR CONSEQUENTIAL DAMAGES OR ANY DAMAGES
 * WHATSOEVER RESULTING FROM LOSS OF USE, DATA OR PROFITS, WHETHER IN AN
 * ACTION OF CONTRACT, NEGLIGENCE OR OTHER TORTIOUS ACTION, ARISING OUT OF
 * OR IN CONNECTION WITH THE USE OR PERFORMANCE OF THIS SOFTWARE.
 */

use App\Kernel;


require_once __DIR__ . '/vendor/autoload.php';

if ( empty( $_SERVER['SCRIPT_FILENAME'] ) )
    return;

$app = function (): Kernel {
    return Kernel::getInstance();
};

if ( !is_object( $app ) )
    throw new TypeError(
        sprintf(
            'Invalid return value: callable object expected, "%s" returned from "%s".',
            get_debug_type( $app ),
            $_SERVER['SCRIPT_FILENAME']
        )
    );

$runtime = $_SERVER['APP_RUNTIME'] ?? $_ENV['APP_RUNTIME'] ?? 'Symfony\\Component\\Runtime\\SymfonyRuntime';
$runtime = new $runtime(
    ( $_SERVER['APP_RUNTIME_OPTIONS'] ?? $_ENV['APP_RUNTIME_OPTIONS'] ?? [] ) + [
        'project_dir' => __DIR__,
    ]
);

[
    $app,
    $args
] = $runtime
    ->getResolver( $app )
    ->resolve();

$app = $app( $args );

exit(
$runtime
    ->getRunner( $app )
    ->run()
);
