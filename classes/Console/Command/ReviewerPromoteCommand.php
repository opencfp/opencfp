<?php

namespace OpenCFP\Console\Command;

use OpenCFP\Console\BaseCommand;
use OpenCFP\Domain\Services\AccountManagement;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class ReviewerPromoteCommand extends BaseCommand
{
    protected function configure()
    {
        $this
            ->setName('reviewer:promote')
            ->setDefinition([
                new InputArgument('email', InputArgument::REQUIRED, 'Email address of user to promote to reviewer'),
            ])
            ->setDescription('Promote an existing user to be a reviewer')
            ->setHelp(
                <<<EOF
The <info>%command.name%</info> command promotes a user to the reviewer group for a given environment:

<info>php %command.full_name% speaker@opencfp.org --env=production</info>
<info>php %command.full_name% speaker@opencfp.org --env=development</info>
EOF
            );
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        /* @var AccountManagement $accounts */
        $accounts = $this->app[AccountManagement::class];

        $email = $input->getArgument('email');

        $io = new SymfonyStyle(
            $input,
            $output
        );

        $io->title('OpenCFP');

        $io->section(sprintf(
            'Promoting account with email %s to Reviewer',
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

        $accounts->promoteTo($user->getLogin(), 'Reviewer');

        $io->success(sprintf(
            'Added account with email %s to the Reviewer group',
            $email
        ));

        return 0;
    }

    public function setApp($app)
    {
        $this->app = $app;
    }
}
