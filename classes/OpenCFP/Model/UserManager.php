<?php

namespace OpenCFP\Model;

use Cartalyst\Sentry\Users\ProviderInterface as UserProviderInterface;
use Cartalyst\Sentry\Groups\ProviderInterface as GroupProviderInterface;

class UserManager implements UserManagerInterface
{
    private $userProvider;
    private $groupProvider;

    public function __construct(UserProviderInterface $userProvider, GroupProviderInterface $groupProvider)
    {
        $this->userProvider  = $userProvider;
        $this->groupProvider = $groupProvider;
    }

    public function createUser(array $data)
    {
        // Force user's account to be activated
        $data['activated'] = 1;

        return $this->userProvider->create($data);
    }

    public function getGroup($name)
    {
        return $this->groupProvider->findByName($name);
    }
}