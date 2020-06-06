<?php

$header = <<<'EOF'
This file is part of {{pkgName}}.

@link     https://github.com/{{pkgName}}
@author   https://github.com/{{author}}
@license  https://github.com/{{pkgName}}/blob/master/LICENSE
EOF;

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

$finder = PhpCsFixer\Finder::create()
    // ->exclude('test')
                           ->exclude('docs')
                           ->exclude('vendor')
                           ->in(__DIR__);

return PhpCsFixer\Config::create()
                        ->setRiskyAllowed(true)
                        ->setRules($rules)
                        ->setFinder($finder)
                        ->setUsingCache(false);
