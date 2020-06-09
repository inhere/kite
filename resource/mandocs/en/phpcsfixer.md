# php cs fixer

github: [PHP-CS-Fixer](https://github.com/FriendsOfPHP/PHP-CS-Fixer)

## install

1. install from github release page
2. install by `brew` on macOS

example:

```bash
brew install php-cs-fixer
```

## config

the `.php_cs` examples: 

```php
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
    'list_syntax' => [
        'syntax' => 'short'
    ],
    'class_attributes_separation' => true,
    'declare_strict_types'        => true,
    'global_namespace_import' => [
        'import_constants' => true,
        'import_functions' => true,
    ],
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

```

## usage

usage examples:

```bash
php-cs-fixer fix
php-cs-fixer fix ./some/path
```
