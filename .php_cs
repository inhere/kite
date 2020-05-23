<?php

$header = <<<'EOF'
This file is part of PTool.

@link     https://github.com/inhere
@author   https://github.com/inhere
@license  MIT
EOF;

return PhpCsFixer\Config::create()
    ->setRiskyAllowed(true)
    ->setRules([
        '@PSR2' => true,
        'array_syntax' => [
            'syntax' => 'short'
        ],
        'class_attributes_separation' => true,
        'declare_strict_types' => true,
        'global_namespace_import' => true,
        'header_comment' => [
            'comment_type' => 'PHPDoc',
            'header'    => $header,
            'separate'  => 'bottom'
        ],
        'no_unused_imports' => true,
        'single_quote' => true,
        'standardize_not_equals' => true,
    ])
    ->setFinder(
        PhpCsFixer\Finder::create()
            ->exclude('test')
            ->exclude('runtime')
            ->exclude('vendor')
            ->in(__DIR__)
    )
    ->setUsingCache(false);
