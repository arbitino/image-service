<?php

return (new PhpCsFixer\Config())
    ->setRules([
        '@PER-CS2.0' => true,
        'declare_strict_types' => true,
        'php_unit_method_casing' => [
            'case' => 'snake_case'
        ]
    ])
    ->setFinder(
        (new PhpCsFixer\Finder())->in([__DIR__.'/src', __DIR__.'/tests'])
    );
