<?php

namespace OpenCFP\Console\Command;

use Cartalyst\Sentry\Sentry;
use Cartalyst\Sentry\Users\UserNotFoundException;
use OpenCFP\Console\BaseCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ClearCacheCommand extends BaseCommand
{
    /**
     * Configure the command options.
     *
     * @return void
     */
    protected function configure()
    {
        $this
            ->setName('cache:clear')
            ->setDescription('Clears the caches');
    }

    /**
     * Execute the command.
     *
     * @param  \Symfony\Component\Console\Input\InputInterface  $input
     * @param  \Symfony\Component\Console\Output\OutputInterface  $output
     * @return void
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('Clearing all caches...');

        $purifierPath = $this->app->cachePurifierPath();
        $output->writeln(sprintf('  <info>✓</info> %s', $purifierPath));
        passthru(sprintf('rm -rf %s/*', $purifierPath));

        $twigPath = $this->app->cacheTwigPath();
        $output->writeln(sprintf('  <info>✓</info> %s', $twigPath));
        passthru(sprintf('rm -rf %s/*', $twigPath));

        $output->writeln('Done!');
    }
} 