<?php

namespace OpenCFP\Console\Command;

use OpenCFP\Console\BaseCommand;
use OpenCFP\Domain\Services\AccountManagement;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ReviewerDemoteCommand extends BaseCommand
{
    protected function configure()
    {
        $this
            ->setName('reviewer:demote')
            ->setDefinition([
                new InputArgument('email', InputArgument::REQUIRED, 'Email address of user to demote'),
            ])
            ->setDescription('Demote an existing user from being an admin')
            ->setHelp(
                <<<EOF
The <info>%command.name%</info> command removes a user from the reviewer group for a given environment:

<info>php %command.full_name% speaker@opencfp.org --env=production</info>
<info>php %command.full_name% speaker@opencfp.org --env=development</info>
EOF
            );
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var AccountManagement $accounts */
        $accounts = $this->app[AccountManagement::class];

        $email = $input->getArgument('email');

        $io = new SymfonyStyle(
            $input,
            $output
        );

        $io->title('OpenCFP');

        $io->section(sprintf(
            'Demoting account with email %s from Reviewer',
            $email
        ));

        try {
            $user = $accounts->findByLogin($email);
        } catch (\Exception $e) {
            $io->error(sprintf(
                'Could not find account with email %s.',
                $email
            ));

            return 1;
        }

        if (! $user->hasAccess('reviewer')) {
            $io->error(sprintf(
                'Account with email %s is not in the Reviewer group.',
                $email
            ));

            return 1;
        }

        $accounts->demoteFrom($user->getLogin(), 'Reviewer');

        $io->success(sprintf(
            'Removed account with email %s from the Reviewer group',
            $email
        ));

        return 0;
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
