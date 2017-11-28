<?php

/**
 * Copyright (c) 2013-2017 OpenCFP
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

final class SentinelAccountManagement implements AccountManagement
{
    /**
     * @var Sentinel
     */
    private $sentinel;

    public function __construct(Sentinel $sentinel)
    {
        $this->sentinel = $sentinel;
    }

    /**
     * @param int $userId
     *
     * @throws UserNotFoundException
     *
     * @return UserInterface
     */
    public function findById($userId): UserInterface
    {
        $user = $this->sentinel->getUserRepository()->findById($userId);

        if ($user instanceof SentinelUserInterface) {
            return new SentinelUser($user, $this->sentinel);
        }

        throw UserNotFoundException::userNotFound($userId);
    }

    /**
     * @param string $email
     *
     * @throws UserNotFoundException
     *
     * @return UserInterface
     */
    public function findByLogin($email): UserInterface
    {
        $user = $this->sentinel->getUserRepository()->findByCredentials(['email' => $email]);
        if ($user instanceof SentinelUserInterface) {
            return new SentinelUser($user, $this->sentinel);
        }

        throw UserNotFoundException::userNotFound($email);
    }

    public function findByRole($role): array
    {
        return $this->sentinel->getRoleRepository()->findByName($role)->getUsers()->toArray();
    }

    public function create($email, $password, array $data = []): UserInterface
    {
        if ($this->sentinel
                ->getUserRepository()
                ->findByCredentials(['email' => $email])
            instanceof SentinelUserInterface
        ) {
            throw UserExistsException::fromEmail($email);
        }
        $user = $this->sentinel
            ->getUserRepository()
            ->create(\array_merge(['email' => $email, 'password' => $password], $data));
        if ($user instanceof SentinelUserInterface) {
            return new SentinelUser($user, $this->sentinel);
        }

        throw new UserExistsException();
    }

    /**
     * @param string $email
     *
     * @throws UserNotFoundException
     *
     * @return bool
     */
    public function activate($email): bool
    {
        $user           = $this->findByLogin($email)->getUser();
        $activationCode = $this->sentinel->getActivationRepository()->create($user)->getCode();

        return $this->sentinel->getActivationRepository()->complete($user, $activationCode);
    }

    public function promoteTo($email, $role = 'Admin')
    {
        $this->sentinel
            ->getRoleRepository()
            ->findByName(\strtolower($role))
            ->users()
            ->attach($this->findByLogin($email)->getId());
    }

    public function demoteFrom($email, $role = 'Admin')
    {
        $this->sentinel
            ->getRoleRepository()
            ->findByName(\strtolower($role))
            ->users()
            ->detach($this->findByLogin($email)->getId());
    }
}
