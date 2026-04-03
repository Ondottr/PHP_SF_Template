#!/bin/sh
# ci-init.sh — Non-interactive bootstrap for CI and staging.
#
# Wires the User entity class into the framework entry points
# (bin/console, public/index.php, tests/bootstrap.php).

set -e

php << 'PHP'
<?php
$use_stmt   = 'use App\Entity\Main\User;';
$set_method = '->setApplicationUserClassName( User::class )';

foreach (['bin/console', 'public/index.php', 'tests/bootstrap.php'] as $file) {
    if (!file_exists($file)) {
        continue;
    }
    $content = file_get_contents($file);

    if (strpos($content, $use_stmt) === false) {
        $content = preg_replace(
            '/^(use (?:App\\\\Kernel|Symfony\\\\Component\\\\Dotenv\\\\Dotenv);)/m',
            '$1' . "\n" . $use_stmt,
            $content,
            1
        );
    }

    if (strpos($content, 'setApplicationUserClassName') === false) {
        $lines = explode("\n", $content);
        for ($i = count($lines) - 1; $i >= 1; $i--) {
            if (trim($lines[$i]) === ';') {
                preg_match('/^([ \t]*)/', $lines[$i - 1], $m);
                $indent = $m[1];
                array_splice($lines, $i, 0, $indent . $set_method);
                break;
            }
        }
        $content = implode("\n", $lines);
    }

    file_put_contents($file, $content);
}
PHP

echo "ci-init.sh: entry points wired."
