<?php

namespace OpenCFP\Console\Command;

use OpenCFP\Console\BaseCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

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
        $io = new SymfonyStyle(
            $input,
            $output
        );

        $io->title('OpenCFP');

        $io->section('Clearing caches');

        $paths = [
            $this->app['path']->cachePurifierPath(),
            $this->app['path']->cacheTwigPath(),
        ];

        \array_walk($paths, function ($path) use ($io) {
            \passthru(\sprintf('rm -rf %s/*', $path));

            $io->writeln(\sprintf(
                '  * %s',
                $path
            ));
        });

        $io->success('Cleared caches.');
    }
}
