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
                new InputOption('role', 'r', InputOption::VALUE_OPTIONAL, "Role of the user to create (speaker, reviewer, admin), default is speaker", null)
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

        // Figure what role was passed in
        $valid_roles = ['speaker', 'reviewer', 'admin'];
        $role = 'speaker';

        if ($input->getOption('role') !== null) {
            $role = $input->getOption('role');
        }

        if (!in_array($role, $valid_roles)) {
            $io->error('You selected an invalid role for the user');
            return 1;
        }

        $user = $this->createUser([
            'first_name' => $input->getOption('first_name'),
            'last_name' => $input->getOption('last_name'),
            'password' => $input->getOption('password'),
            'email' => $input->getOption('email'),
            'role' => $role
        ]);
        if ($user == false) {
            $io->error('User Already Exists!');
            return 1;
        }

        $io->success('User Created!');
    }

    private function createUser($data)
    {
        try {
            $credentials = [
                'first_name' => $data['first_name'] ?: null,
                'last_name' => $data['last_name'] ?: null,
                'email' => $data['email'] ?: null,
                'password' => $data['password'] ?: null,
            ];
            $sentinel = $this->app['sentinel'];
            $user = $sentinel::registerAndActivate($credentials);
            $role = $sentinel::findRoleBySlug($data['role']);
            $role->users()->attach($user);

            return $user;
        } catch (Exception $e) {
            echo $e->getMessage(). "\n";
            return false;
        }
    }
}
