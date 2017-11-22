<?php

namespace OpenCFP\Infrastructure\Auth;

use Cartalyst\Sentinel\Sentinel;
use OpenCFP\Domain\Services\Authentication;
use OpenCFP\Domain\Services\AuthenticationException;
use OpenCFP\Domain\Services\NotAuthenticatedException;

class SentinelAuthentication implements Authentication
{
    /**
     * @var Sentinel
     */
    private $sentinel;

    /**
     * @var SentinelAccountManagement
     */
    private $accountManagement;

    public function __construct(Sentinel $sentinel, SentinelAccountManagement $accountManagement)
    {
        $this->sentinel          = $sentinel;
        $this->accountManagement = $accountManagement;
    }

    /**
     * Given valid credentials, authenticate the user.
     *
     * @param string $username
     * @param string $password
     *
     * @throws AuthenticationException
     */
    public function authenticate($username, $password)
    {
        try {
            $success = false;
            $user    = $this->accountManagement->findByLogin($username);
            if ($user->checkPassword($password)) {
                $success = $this->sentinel->login($user->getUser());
            }
            if (!$success) {
                throw new AuthenticationException('Failure to login.');
            }
        } catch (\Throwable $e) {
            throw new AuthenticationException('Failure to login.');
        }
    }

    /**
     * Returns current authenticated User account.
     *
     * @throws NotAuthenticatedException
     *
     * @return UserInterface
     */
    public function user(): UserInterface
    {
        $user = $this->sentinel->getUser();

        return new SentinelUser($user, $this->sentinel);
    }

    /**
     * Returns current authenticated User Id.
     *
     * @throws NotAuthenticatedException
     *
     * @return int
     */
    public function userId(): int
    {
        return $this->sentinel->getUser()->getUserId();
    }

    /**
     * Determines whether or not the user is logged in.
     *
     * @return bool
     */
    public function check(): bool
    {
        return $this->sentinel->check() !== false;
    }

    /**
     * Determine whether the user is a non-authenticated guest.
     *
     * @return bool
     */
    public function guest(): bool
    {
        return !$this->sentinel->check();
    }

    /**
     * Destroys the user's active authenticated session.
     */
    public function logout()
    {
        return $this->sentinel->logout();
    }
}
