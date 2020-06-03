<?php

$header = <<<'EOF'
This file is part of {{pkgName}}.

@link     https://github.com/{{pkgName}}
@author   https://github.com/{{author}}
@license  MIT
EOF;

$finder = PhpCsFixer\Finder::create()
    // ->exclude('test')
                           ->exclude('runtime')->exclude('vendor')->in(__DIR__);

$rules = [
    '@PSR2'                       => true,
    'array_syntax'                => [
        'syntax' => 'short'
    ],
    'class_attributes_separation' => true,
    'declare_strict_types'        => true,
    'global_namespace_import'     => true,
    'header_comment'              => [
        'comment_type' => 'PHPDoc',
        'header'       => $header,
        'separate'     => 'bottom'
    ],
    'no_unused_imports'           => true,
    'single_quote'                => true,
    'standardize_not_equals'      => true,
];

return PhpCsFixer\Config::create()->setRiskyAllowed(true)->setRules($rules)->setFinder($finder)->setUsingCache(false);
