<?php

namespace OpenCFP\Infrastructure\Auth;

class User implements UserInterface
{
    /**
     * @var \Cartalyst\Sentry\Users\UserInterface
     */
    private $user;

    public function __construct(\Cartalyst\Sentry\Users\UserInterface $user)
    {
        $this->user= $user;
    }

    /**
     * Retrieves the user's Id
     *
     * @return int
     */
    public function getId(): int
    {
        return $this->user->getId();
    }

    /**
     * Retrieves the users login (email)
     *
     * @return string
     */
    public function getLogin(): string
    {
        return $this->user->getLogin();
    }

    /**
     * @param $permissions
     *
     * @return mixed
     */
    public function hasAccess($permissions)
    {
        return $this->user->hasAccess($permissions);
    }

    /**
     * Checks if the given matches the current password.
     *
     * @param string $password
     *
     * @return bool
     */
    public function checkPassword(string $password): bool
    {
        return $this->user->checkPassword($password);
    }

    /**
     * Checks if the provided user reset password code is
     * valid without actually resetting the password.
     *
     * @param string $resetCode
     *
     * @return bool
     */
    public function checkResetPasswordCode(string $resetCode): bool
    {
        return $this->user->checkResetPasswordCode($resetCode);
    }

    /**
     * Get a reset password code for the given user.
     *
     * @return string
     */
    public function getResetPasswordCode(): string
    {
        return $this->user->getResetPasswordCode();
    }

    /**
     * Attempts to reset a user's password by matching
     * the reset code generated with the user's.
     *
     * @param string $resetCode
     * @param string $newPassword
     *
     * @return bool
     */
    public function attemptResetPassword($resetCode, $newPassword): bool
    {
        return $this->user->attemptResetPassword($resetCode, $newPassword);
    }
}
