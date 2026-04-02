<?php declare(strict_types=1);

use PhpCsFixer\Config;
use PhpCsFixer\Finder;

$finder = Finder::create()
    ->in([
        __DIR__ . '/src',
        __DIR__ . '/tests',
    ])
    ->exclude([
        'var',
        'vendor',
    ]);

return (new Config())
    ->setRiskyAllowed(true)
    ->setFinder($finder)
    ->setRules([
        '@PSR2' => true,
        // Base rule sets
        '@Symfony' => true,

        // --- Modern PHP / Symfony ---
        'declare_strict_types' => true,
        'ordered_imports' => [
            'sort_algorithm' => 'alpha',
        ],
        'no_unused_imports' => true,

        // --- Arrays ---
        'array_syntax' => ['syntax' => 'short'],

        // --- Readability ---
        'not_operator_with_successor_space' => false,
        'unary_operator_spaces' => true,

        // Keep method/function calls fully multiline when they have multiline arguments
        'method_argument_space' => [
            'on_multiline' => 'ensure_fully_multiline',
        ],
        'single_line_throw' => false, // keeps throw(...) multiline even if fits in one line

        'method_chaining_indentation' => true,

        'trailing_comma_in_multiline' => [
            'elements' => ['arrays', 'arguments', 'parameters'],
        ],

        'ordered_class_elements' => true,

        'global_namespace_import' => [
            'import_classes' => false,
            'import_functions' => false,
            'import_constants' => false,
        ],

        // --- Strings ---
        'single_quote' => true,

        // --- Control structures ---
        'control_structure_braces' => true,
        'control_structure_continuation_position' => [
            'position' => 'same_line',
        ],

        // --- Clean code ---
        'phpdoc_to_comment' => false, // keep /** for all multiline comments
        'no_useless_else' => true,
        'no_useless_return' => true,

        // --- Risky but useful ---
        'strict_comparison' => true,
        'strict_param' => true,

        // --- Keep your style ---
        'binary_operator_spaces' => [
            'default' => 'single_space',
        ],

        // --- Concatenation spacing ---
        'concat_space' => ['spacing' => 'one'],
    ]);
