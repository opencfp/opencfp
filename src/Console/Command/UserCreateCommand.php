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
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

final class UserCreateCommand extends Command
{
    /**
     * @var Services\AccountManagement
     */
    private $accountManagement;

    public function __construct(Services\AccountManagement $accountManagement)
    {
        parent::__construct('user:create');

        $this->accountManagement = $accountManagement;
    }

    protected function configure()
    {
        $this
            ->setDescription('Creates a new user')
            ->setDefinition([
                new InputOption('first_name', 'f', InputOption::VALUE_REQUIRED, 'First Name of the user to create', null),
                new InputOption('last_name', 'l', InputOption::VALUE_REQUIRED, 'Last Name of the user to create', null),
                new InputOption('email', 'm', InputOption::VALUE_REQUIRED, 'Email of the user to create', null),
                new InputOption('password', 'p', InputOption::VALUE_REQUIRED, 'Password of the user to create', null),
                new InputOption('admin', 'a', InputOption::VALUE_NONE, 'Promote to administrator', null),
                new InputOption('reviewer', 'r', InputOption::VALUE_NONE, 'Promote to reviewer', null),
            ]);
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle(
            $input,
            $output
        );

        $io->title('OpenCFP');

        $io->section('Creating User');

        $data = [
            'first_name' => $input->getOption('first_name'),
            'last_name'  => $input->getOption('last_name'),
            'email'      => $input->getOption('email'),
            'password'   => $input->getOption('password'),
        ];

        try {
            $user = $this->accountManagement->create(
                $data['email'],
                $data['password'],
                $data
            );
        } catch (Auth\UserExistsException $exception) {
            $io->error(\sprintf(
                'A user with the login "%s" already exists.',
                $data['email']
            ));

            return 1;
        }

        $io->writeln(\sprintf(
            ' * created user with login <info>%s</info>',
            $data['email']
        ));

        $this->accountManagement->activate($data['email']);

        $roles = [];

        if ($input->getOption('admin')) {
            $roles[] = 'admin';
        }

        if ($input->getOption('reviewer')) {
            $roles[] = 'reviewer';
        }

        if (\count($roles)) {
            foreach ($roles as $role) {
                if ($user->hasAccess($role)) {
                    continue;
                }

                $this->accountManagement->promoteTo(
                    $user->getLogin(),
                    $role
                );

                $io->writeln(\sprintf(
                    ' * promoted user to <info>%s</info>',
                    $role
                ));
            }
        }

        $io->success('User Created');
    }
}
