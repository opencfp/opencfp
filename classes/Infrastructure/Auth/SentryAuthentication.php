<?php

namespace OpenCFP\Infrastructure\Auth;

use Cartalyst\Sentry\Sentry;
use OpenCFP\Domain\Services\Authentication;
use OpenCFP\Domain\Services\AuthenticationException;
use OpenCFP\Domain\Services\NotAuthenticatedException;

class SentryAuthentication implements Authentication
{

    /**
     * @var Sentry
     */
    private $sentry;

    public function __construct(Sentry $sentry)
    {
        $this->sentry = $sentry;
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
            $this->sentry->authenticate(['email' => $username, 'password' => $password]);
        } catch (\Exception $e) {
            throw new AuthenticationException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * Returns current authenticated User account.
     *
     * @throws NotAuthenticatedException
     *
     * @return UserInterface
     *
     */
    public function user(): UserInterface
    {
        $user = $this->sentry->getUser();

        if (!$user) {
            throw new NotAuthenticatedException();
        }

        return new SentryUser($user);
    }

    public function userId(): int
    {
        return (int) $this->user()
            ->getId();
    }

    /**
     * Determines whether or not the user is logged in.
     *
     * @return bool
     */
    public function check(): bool
    {
        return $this->sentry->check();
    }

    /**
     * Determine whether the user is a non-authenticated guest.
     *
     * @return bool
     */
    public function guest(): bool
    {
        return ! $this->check();
    }

    /**
     * Destroys the user's active authenticated session.
     *
     * @throws NotAuthenticatedException
     */
    public function logout()
    {
        $this->sentry->logout();
    }
}
