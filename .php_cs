<?php

$finder = PhpCsFixer\Finder::create()
    ->exclude('migrations')
    ->in(__DIR__);

$config = PhpCsFixer\Config::create()
    ->setUsingCache(true)
    ->setRiskyAllowed(true)
    ->setRules([
        '@PSR2' => true,
        'array_syntax' => [
            'syntax' => 'short'
        ],
        'binary_operator_spaces' => [
            'default' => 'align',
        ],
        'blank_line_after_opening_tag' => true,
        'blank_line_before_statement' => true,
        'cast_spaces' => true,
        'concat_space' => [
            'spacing' => 'one',
        ],
        'increment_style' => true,
        'method_separation' => true,
        'no_alias_functions' => true,
        'no_empty_phpdoc' => true,
        'no_extra_consecutive_blank_lines' => true,
        'no_multiline_whitespace_before_semicolons' => true,
        'no_trailing_comma_in_singleline_array' => true,
        'no_unused_imports' => true,
        'no_useless_else' => true,
        'ordered_imports' => true,
        'phpdoc_align' => true,
        'phpdoc_indent' => true,
        'phpdoc_no_empty_return' => true,
        'phpdoc_no_package' => true,
        'phpdoc_order' => true,
        'phpdoc_scalar' => true,
        'phpdoc_separation' => true,
        'phpdoc_trim' => true,
        'phpdoc_types_order' => true,
        'php_unit_expectation' => true,
        'php_unit_no_expectation_annotation' => true,
        'php_unit_test_class_requires_covers' => true,
        'psr0' => false,
        'return_type_declaration' => true,
        'single_blank_line_before_namespace' => true,
        'single_quote' => true,
        'trailing_comma_in_multiline_array' => true,
        'trim_array_spaces' => true,
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
