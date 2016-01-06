<?php

$finder = Symfony\CS\Finder\DefaultFinder::create()
    ->exclude('migrations')
    ->in(__DIR__);

return Symfony\CS\Config\Config::create()
    ->setUsingCache(true)
    ->level(Symfony\CS\FixerInterface::PSR2_LEVEL)
    ->fixers([
        '-psr0',
        'multiline_array_trailing_comma',
        'ordered_use',
        'short_array_syntax',
        'unused_use',
    ])
    ->finder($finder)
;
