<?php

namespace OpenCFP\Console\Command;

use OpenCFP\Console\BaseCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ClearCacheCommand extends BaseCommand
{
    protected function configure()
    {
        $this
            ->setName('cache:clear')
            ->setDescription('Clears the caches');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('Clearing all caches...');

        $paths = [
            $this->app->cachePurifierPath(),
            $this->app->cacheTwigPath(),
        ];

        array_walk($paths, function ($path) use ($output) {
            $output->writeln(sprintf('  <info>✓</info> %s', $path));
            passthru(sprintf('rm -rf %s/*', $path));
        });

        $output->writeln('Done!');
    }
}
