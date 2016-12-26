<?php

$finder = PhpCsFixer\Finder::create()
    ->exclude('migrations')
    ->in(__DIR__);

$config = PhpCsFixer\Config::create()
    ->setUsingCache(true)
    ->setRules([
        '@PSR2' => true,
        'array_syntax' => [
            'syntax' => 'short'
        ],
        'no_unused_imports' => true,
        'ordered_imports' => true,
        'psr0' => false,
        'trailing_comma_in_multiline_array' => true,
    ])
    ->setFinder($finder);

if (getenv('TRAVIS')) {
    $config->setCacheFile(getenv('HOME') . '/.php-cs-fixer/.php_cs.cache');
}

return $config;
