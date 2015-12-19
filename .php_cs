<?php

$finder = Symfony\CS\Finder\DefaultFinder::create()
    ->exclude('migrations')
    ->in(__DIR__);

return Symfony\CS\Config\Config::create()
    ->setUsingCache(true)
    ->level(Symfony\CS\FixerInterface::PSR2_LEVEL)
    ->fixers([
        '-psr0',
        'ordered_use',
    ])
    ->finder($finder)
;
