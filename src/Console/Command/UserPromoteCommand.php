<?php

declare(strict_types=1);

/**
 * Copyright (c) 2013-2019 OpenCFP
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

final class UserPromoteCommand extends Command
{
    /**
     * @var Services\AccountManagement
     */
    private $accountManagement;

    public function __construct(Services\AccountManagement $accountManagement)
    {
        parent::__construct('user:promote');

        $this->accountManagement = $accountManagement;
    }

    protected function configure()
    {
        $this
            ->setDescription('Promote an existing user to a role')
            ->setDefinition([
                new InputArgument('email', InputArgument::REQUIRED, 'Email address of user'),
                new InputArgument('role-name', InputArgument::REQUIRED, 'Name of role user should be promoted to'),
            ])
            ->setHelp(
                <<<EOF
The <info>%command.name%</info> command promotes a user to a role group for a given environment:

<info>php %command.full_name% <email> <role-name> --env=production</info>
<info>php %command.full_name% <email> <role-name> --env=development</info>
EOF
            );
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $email    = $input->getArgument('email');
        $roleName = $input->getArgument('role-name');

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
            $this->accountManagement->promoteTo(
                $email,
                $roleName
            );
        } catch (Auth\UserNotFoundException $exception) {
            $io->error(\sprintf(
                'Could not find account with email "%s".',
                $email
            ));

            return 1;
        } catch (Auth\RoleNotFoundException $exception) {
            $io->error(\sprintf(
                'Could not find role with name "%s".',
                $roleName
            ));

            return 1;
        } catch (\Exception $exception) {
            $io->error(\sprintf(
                'Could not promote account with email "%s".',
                $email
            ));

            return 1;
        }

        $io->success(\sprintf(
            'Added account with email "%s" to the "%s" group.',
            $email,
            $roleName
        ));
    }
}
