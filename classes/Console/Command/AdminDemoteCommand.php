<?php

namespace OpenCFP\Console\Command;

use OpenCFP\Console\BaseCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class AdminDemoteCommand extends BaseCommand
{
    protected function configure()
    {
        $this
            ->setName('admin:demote')
            ->setDefinition([
                new InputArgument('email', InputArgument::REQUIRED, 'Email address of user to demote'),
            ])
            ->setDescription('Demote an existing user from being an admin')
            ->setHelp(
                <<<EOF
The <info>%command.name%</info> command removes a user from the admin group for a given environment:

<info>php %command.full_name% speaker@opencfp.org --env=production</info>
<info>php %command.full_name% speaker@opencfp.org --env=development</info>
EOF
);
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        return $this->demote($input, $output, 'Admin');
    }

    /**
     * Method used to inject a OpenCFP\Application object into the command
     * for testing purposes
     *
     * @param $app \OpenCFP\Application
     */
    public function setApp($app)
    {
        $this->app = $app;
    }
}
