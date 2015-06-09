<?php

namespace OpenCFP\Console\Command;

use Cartalyst\Sentry\Sentry;
use Cartalyst\Sentry\Users\UserNotFoundException;
use OpenCFP\Console\BaseCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class AdminDemoteCommand extends BaseCommand
{
    /**
     * Configure the command options.
     *
     * @return void
     */
    protected function configure()
    {
        $this
            ->setName('admin:demote')
            ->setDefinition([
                new InputArgument('email', InputArgument::REQUIRED, 'Email address of user to demote')
            ])
            ->setDescription('Demote an existing user from being an admin')
            ->setHelp(<<<EOF
The <info>%command.name%</info> command removes a user from the admin group for a given environment:

<info>php %command.full_name% speaker@opencfp.org --env=production</info>
<info>php %command.full_name% speaker@opencfp.org --env=development</info>
EOF
);
    }

    /**
     * Execute the command.
     *
     * @param  \Symfony\Component\Console\Input\InputInterface   $input
     * @param  \Symfony\Component\Console\Output\OutputInterface $output
     * @return void
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var Sentry $sentry */
        $sentry = $this->app['sentry'];
        $email = $input->getArgument('email');

        $output->writeln(sprintf('Retrieving account from <info>%s</info>...', $email));

        try {
            $user = $sentry->getUserProvider()->findByLogin($email);
            $output->writeln('  Found account...');

            if ( ! $user->hasAccess('admin')) {
                $output->writeln(sprintf('The account <info>%s</info> is not in the Admin group', $email));
                exit(1);
            }

            $adminGroup = $sentry->getGroupProvider()->findByName('Admin');
            $user->removeGroup($adminGroup);
            $output->writeln(sprintf('  Removed <info>%s</info> from the Admin group', $email));
        } catch (UserNotFoundException $e) {
            $output->writeln(sprintf('<error>Error:</error> Could not find user by %s', $email));
            exit(1);
        }

        $output->writeln('Done!');
        exit(0);
    }
}
