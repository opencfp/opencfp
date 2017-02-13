<?php

namespace OpenCFP\Console\Command;

use Cartalyst\Sentry\Sentry;
use Cartalyst\Sentry\Users\UserNotFoundException;
use OpenCFP\Console\BaseCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

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
        /* @var Sentry $sentry */
        $sentry = $this->app['sentry'];

        $email = $input->getArgument('email');

        $io = new SymfonyStyle(
            $input,
            $output
        );

        $io->title('OpenCFP');

        $io->section(sprintf(
            'Promoting account with email %s to Admin',
            $email
        ));

        try {
            $user = $sentry->getUserProvider()->findByLogin($email);
        } catch (UserNotFoundException $e) {
            $io->error(sprintf(
                'Could not find account with email %s.',
                $email
            ));

            return 1;
        }

        if ($user->hasAccess('admin')) {
            $io->error(sprintf(
                'Account with email %s already is in the Admin group.',
                $email
            ));

            return 1;
        }

        $adminGroup = $sentry->getGroupProvider()->findByName('Admin');
        $user->addGroup($adminGroup);

        $io->success(sprintf(
            'Added account with email %s to the Admin group',
            $email
        ));

        return 0;
    }

    public function setApp($app)
    {
        $this->app = $app;
    }
}
