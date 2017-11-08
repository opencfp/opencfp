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
        'blank_line_after_opening_tag' => true,
        'no_extra_consecutive_blank_lines' => true,
        'no_unused_imports' => true,
        'ordered_imports' => true,
        'psr0' => false,
        'single_blank_line_before_namespace' => true,
        'return_type_declaration' => true,
        'single_quote' => true,
        'trailing_comma_in_multiline_array' => true,
        'yoda_style' => [
            'equal' => false,
            'identical' => false,
            'less_and_greater' => false,
        ],
    ])
    ->setFinder($finder);

if (getenv('TRAVIS')) {
    $config->setCacheFile(getenv('HOME') . '/.php-cs-fixer/.php_cs.cache');
}

return $config;
