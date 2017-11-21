<?php

namespace OpenCFP\Console;

use OpenCFP\Application as ApplicationContainer;
use OpenCFP\Domain\Services\AccountManagement;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

abstract class BaseCommand extends Command
{
    /**
     * @var ApplicationContainer
     */
    protected $app;

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        // Because colors are coolest ...
        $output->setDecorated(true);

        // Since this is considered "framework layer" and has little churn...
        // Probably okay.
        $this->app = $this->getApplication()->getContainer();
    }

    /**
     * Generic demote action, to demote from a specific role
     *
     * @param InputInterface  $input
     * @param OutputInterface $output
     * @param string          $role   Role to demote from, e.g. 'Admin' or 'Reviewer' (Note the capitalization)
     *
     * @return int
     */
    protected function demote(InputInterface $input, OutputInterface $output, string $role)
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
            'Demoting account with email %s from ' . $role,
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

        if (! $user->hasAccess(\strtolower($role))) {
            $io->error(sprintf(
                'Account with email %s is not in the Reviewer group.',
                $email
            ));

            return 1;
        }

        $accounts->demoteFrom($user->getLogin(), $role);

        $io->success(sprintf(
            'Removed account with email %s from the Reviewer group',
            $email
        ));

        return 0;
    }

    /**
     * Generic promote action, to promote to specific role.
     *
     * @param InputInterface  $input
     * @param OutputInterface $output
     * @param string          $role   Role to promote to, e.g. 'Admin' or 'Reviewer' (Note the capitalization)
     *
     * @return int
     */
    public function promote(InputInterface $input, OutputInterface $output, string $role)
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
            'Promoting account with email %s to ' . $role,
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

        $accounts->promoteTo($user->getLogin(), $role);

        $io->success(sprintf(
            'Added account with email %s to the ' . $role . ' group',
            $email
        ));

        return 0;
    }
}
