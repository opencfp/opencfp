<?php

namespace OpenCFP\Console;

use OpenCFP\Application as ApplicationContainer;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class BaseCommand extends Command
{
    /**
     * @var ApplicationContainer
     */
    protected $app;

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        // Because colors are coolest ...
        $output->setDecorated(true);

        // TODO: Violation of law of demeter.
        // Since this is considered "framework layer" and has little churn...
        // Probably okay.
        $this->app = $this->getApplication()->getContainer();
    }
} 