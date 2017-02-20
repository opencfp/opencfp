<?php

namespace OpenCFP\Console\Command;

use OpenCFP\Console\BaseCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class UserAssignRoleCommand extends BaseCommand
{
    protected $app;

    protected function configure()
    {
        $this
            ->setName('user:assignrole')
            ->setDefinition([
                new InputOption('email', 'e', InputOption::VALUE_REQUIRED, 'Email of user to assign role to', null),
                new InputOption('role', 'r', InputOption::VALUE_OPTIONAL, "Role to assign to user", null)
            ])
            ->setDescription('Assign a user to a specific role');
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

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle(
            $input,
            $output
        );
        $io->title('OpenCFP');
        $io->section('Assigning user role');

        // Figure what role was passed in
        $valid_roles = ['speaker', 'reviewer', 'admin'];
        $role = $input->getOption('role');
        $email = $input->getOption('email');

        if (!in_array($role, $valid_roles)) {
            $io->error('You selected an invalid role for the user');
            return 1;
        }

        $user = $this->app['sentinel']::findByCredentials(['email' => $email]);
        $user_roles = $user->roles;

        foreach ($user_roles as $user_role) {
            if ($user_role->slug == $role) {
                $io->error('This user already has been assigned that role');
                return 1;
            }
        }

        $this->app['sentinel']::findRoleBySlug($role)->users()->attach($user);
        $io->success('Role changed');
        return 0;
    }
}
