<?php

namespace OpenCFP\Console\Command;

use Cartalyst\Sentry\Sentry;
use Cartalyst\Sentry\Users\UserNotFoundException;
use OpenCFP\Console\BaseCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class AdminPromoteCommand extends BaseCommand
{
    protected function configure()
    {
        $this
            ->setName('admin:promote')
            ->setDefinition([
                new InputArgument('email', InputArgument::REQUIRED, 'Email address of user to promote to admin'),
            ])
            ->setDescription('Promote an existing user to be an admin')
            ->setHelp(<<<EOF
The <info>%command.name%</info> command promotes a user to the admin group for a given environment:

<info>php %command.full_name% speaker@opencfp.org --env=production</info>
<info>php %command.full_name% speaker@opencfp.org --env=development</info>
EOF
);
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var Sentry $sentry */
        $sentry = $this->app['sentry'];
        $email = $input->getArgument('email');

        $output->writeln(sprintf('Retrieving account from <info>%s</info>...', $email));

        try {
            $user = $sentry->getUserProvider()->findByLogin($email);
            $output->writeln('  Found account...');

            if ($user->hasAccess('admin')) {
                $output->writeln(sprintf('The account <info>%s</info> already has Admin access', $email));

                return 1;
            }

            $adminGroup = $sentry->getGroupProvider()->findByName('Admin');
            $user->addGroup($adminGroup);
            $output->writeln(sprintf('  Added <info>%s</info> to the Admin group', $email));
        } catch (UserNotFoundException $e) {
            $output->writeln(sprintf('<error>Error:</error> Could not find user by %s', $email));

            return 1;
        }

        $output->writeln('Done!');
    }
}
