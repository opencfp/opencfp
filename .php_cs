<?php

$finder = Symfony\CS\Finder\DefaultFinder::create()->in(__DIR__);

return Symfony\CS\Config\Config::create()
    ->setUsingCache(true)
    ->level(Symfony\CS\FixerInterface::PSR2_LEVEL)
    ->finder($finder)
;
