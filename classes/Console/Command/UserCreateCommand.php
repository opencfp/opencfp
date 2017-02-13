<?php

namespace OpenCFP\Console\Command;

use OpenCFP\Console\BaseCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class UserCreateCommand extends BaseCommand
{
    protected function configure()
    {
        $this
            ->setName('user:create')
            ->setDefinition([
                new InputOption('first_name', 'f', InputOption::VALUE_REQUIRED, 'First Name of the user to create', null),
                new InputOption('last_name', 'l', InputOption::VALUE_REQUIRED, 'Last Name of the user to create', null),
                new InputOption('email', 'e', InputOption::VALUE_REQUIRED, 'Email of the user to create', null),
                new InputOption('password', 'p', InputOption::VALUE_REQUIRED, 'Password of the user to create', null),
                new InputOption('admin', 'a', InputOption::VALUE_NONE, 'Promote to administrator', null),
            ])
            ->setDescription('Creates a new user');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle(
            $input,
            $output
        );

        $io->title('OpenCFP');

        $io->section('Creating User');

        $user = $this->createUser([
            'first_name' => $input->getOption('first_name'),
            'last_name' => $input->getOption('last_name'),
            'password' => $input->getOption('password'),
            'email' => $input->getOption('email'),
        ]);

        if ($user == false) {
            $io->error('User Already Exists!');
            return 1;
        }

        $io->block('Account was created');

        if ($input->getOption('admin')) {
            $io->block('Promoting to admin.');
            $this->promote($user);
        }

        $io->success('User Created!');
    }

    private function createUser($data)
    {
        try {
            $user_data = [
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'email' => $data['email'],
                'password' => $data['password'],
                'activated' => 1,
            ];

            /* @var Sentry $sentry */
            $sentry = $this->app['sentry'];


            $user = $sentry->getUserProvider()->create($user_data);


            return $user;
        } catch (UserExistsException $e) {
            return false;
        }
    }

    private function promote($user)
    {
        if ($user->hasAccess('admin')) {
            $io->error(sprintf(
                'Account with email %s already is in the Admin group.',
                $email
            ));


            return false;
        }

        $sentry = $this->app['sentry'];
        $adminGroup = $sentry->getGroupProvider()->findByName('Admin');
        $user->addGroup($adminGroup);

        return true;
    }
}
