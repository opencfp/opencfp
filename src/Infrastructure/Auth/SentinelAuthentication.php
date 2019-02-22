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

namespace OpenCFP\Infrastructure\Auth;

use Cartalyst\Sentinel\Sentinel;
use Cartalyst\Sentinel\Users\UserInterface as SentinelUserInterface;
use OpenCFP\Domain\Services\AccountManagement;
use OpenCFP\Domain\Services\Authentication;
use OpenCFP\Domain\Services\AuthenticationException;
use OpenCFP\Domain\Services\NotAuthenticatedException;

final class SentinelAuthentication implements Authentication
{
    /**
     * @var Sentinel
     */
    private $sentinel;

    /**
     * @var AccountManagement
     */
    private $accountManagement;

    public function __construct(Sentinel $sentinel, AccountManagement $accountManagement)
    {
        $this->sentinel          = $sentinel;
        $this->accountManagement = $accountManagement;
    }

    public function authenticate($username, $password)
    {
        try {
            $success = false;
            $user    = $this->accountManagement->findByLogin($username);

            if ($user->checkPassword($password)) {
                $success = $this->sentinel->login($user->getUser());
            }

            if (!$success) {
                throw AuthenticationException::loginFailure();
            }
        } catch (UserNotFoundException $e) {
            throw $e;
        } catch (\Throwable $e) {
            throw AuthenticationException::loginFailure();
        }
    }

    public function user(): UserInterface
    {
        $user = $this->sentinel->getUser();

        if ($user instanceof SentinelUserInterface) {
            return new SentinelUser($user, $this->sentinel);
        }

        throw new NotAuthenticatedException();
    }

    public function isAuthenticated(): bool
    {
        return $this->sentinel->check() !== false;
    }

    public function logout(): bool
    {
        return $this->sentinel->logout() !== false;
    }
}
