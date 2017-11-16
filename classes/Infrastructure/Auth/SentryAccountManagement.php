<?php

namespace OpenCFP\Infrastructure\Auth;

use Cartalyst\Sentry\Sentry;
use Cartalyst\Sentry\Users\UserAlreadyActivatedException;
use OpenCFP\Domain\Services\AccountManagement;

class SentryAccountManagement implements AccountManagement
{
    /**
     * @var Sentry
     */
    private $sentry;

    public function __construct(Sentry $sentry)
    {
        $this->sentry = $sentry;
    }

    public function findById($userId): UserInterface
    {
        return new User($this->sentry->findUserById($userId));
    }

    public function findByLogin($email): UserInterface
    {
        return new User($this->sentry->findUserByLogin($email));
    }

    public function findByRole($role): array
    {
        return $this->sentry->findAllUsersInGroup(
            $this->sentry->findGroupByName($role)
        )->toArray();
    }

    public function create($email, $password, array $data = []): UserInterface
    {
        $user = $this->sentry->createUser(array_merge([
            'email'    => $email,
            'password' => $password,
        ], $data));

        $user->addGroup(
            $this->sentry->findGroupByName('Speakers')
        );

        return new User($user);
    }

    public function activate($email)
    {
        $user = $this->findByLogin($email)->getUser();
        $code = $user->getActivationCode();

        try {
            $user->attemptActivation($code);
        } catch (UserAlreadyActivatedException $e) {
            // Do nothing
        }
    }

    public function promoteTo($email, $role = 'Admin')
    {
        $this->findByLogin($email)->getUser()->addGroup(
            $this->sentry->findGroupByName($role)
        );
    }

    public function demoteFrom($email, $role = 'Admin')
    {
        $this->findByLogin($email)->getUser()->removeGroup(
            $this->sentry->findGroupByName($role)
        );
    }
}
