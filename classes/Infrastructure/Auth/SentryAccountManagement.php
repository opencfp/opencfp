<?php

namespace OpenCFP\Infrastructure\Auth;

use Cartalyst\Sentry\Sentry;
use Cartalyst\Sentry\Users\UserAlreadyActivatedException;
use Cartalyst\Sentry\Users\UserInterface;
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
        return $this->sentry->findUserById($userId);
    }

    public function findByLogin($email): UserInterface
    {
        return $this->sentry->findUserByLogin($email);
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
            'email' => $email,
            'password' => $password,
        ], $data));

        $user->addGroup(
            $this->sentry->findGroupByName('Speakers')
        );

        return $user;
    }

    public function activate($email)
    {
        $user = $this->findByLogin($email);
        $code = $user->getActivationCode();

        try {
            $user->attemptActivation($code);
        } catch (UserAlreadyActivatedException $e) {
            // Do nothing
        }
    }

    public function promote($email)
    {
        $this->findByLogin($email)->addGroup(
            $this->sentry->findGroupByName('Admin')
        );
    }

    public function demote($email)
    {
        $this->findByLogin($email)->removeGroup(
            $this->sentry->findGroupByName('Admin')
        );
    }
}