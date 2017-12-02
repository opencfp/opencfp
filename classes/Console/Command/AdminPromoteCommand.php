<?php

declare(strict_types=1);

/**
 * Copyright (c) 2013-2017 OpenCFP
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @see https://github.com/opencfp/opencfp
 */

namespace OpenCFP\Console\Command;

use OpenCFP\Domain\Services;
use OpenCFP\Infrastructure\Auth;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

final class AdminPromoteCommand extends Command
{
    /**
     * @var Services\AccountManagement
     */
    private $accountManagement;

    public function __construct(Services\AccountManagement $accountManagement)
    {
        parent::__construct('admin:promote');

        $this->accountManagement = $accountManagement;
    }

    protected function configure()
    {
        $this
            ->setDescription('Promote an existing user to be an admin')
            ->setDefinition([
                new InputArgument('email', InputArgument::REQUIRED, 'Email address of user to promote to admin'),
            ])
            ->setHelp(
                <<<EOF
The <info>%command.name%</info> command promotes a user to the admin group for a given environment:

<info>php %command.full_name% speaker@opencfp.org --env=production</info>
<info>php %command.full_name% speaker@opencfp.org --env=development</info>
EOF
            );
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $email = $input->getArgument('email');

        $roleName = 'Admin';

        $io = new SymfonyStyle(
            $input,
            $output
        );

        $io->title('OpenCFP');

        $io->section(\sprintf(
            'Promoting account with email "%s" to "%s"',
            $email,
            $roleName
        ));

        try {
            $this->accountManagement->findByLogin($email);
        } catch (Auth\UserNotFoundException $exception) {
            $io->error(\sprintf(
                'Could not find account with email "%s".',
                $email
            ));

            return 1;
        }

        $this->accountManagement->promoteTo(
            $email,
            $roleName
        );

        $io->success(\sprintf(
            'Added account with email "%s" to the "%s" group',
            $email,
            $roleName
        ));
    }
}
