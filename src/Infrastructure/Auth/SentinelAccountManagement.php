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

use Cartalyst\Sentinel\Roles;
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

    public function findById(int $userId): UserInterface
    {
        $user = $this->sentinel->getUserRepository()->findById($userId);

        if ($user instanceof SentinelUserInterface) {
            return new SentinelUser($user, $this->sentinel);
        }

        throw UserNotFoundException::fromId($userId);
    }

    public function findByLogin(string $email): UserInterface
    {
        $user = $this->sentinel->getUserRepository()->findByCredentials(['email' => $email]);

        if ($user instanceof SentinelUserInterface) {
            return new SentinelUser($user, $this->sentinel);
        }

        throw UserNotFoundException::fromEmail($email);
    }

    public function findByRole(string $name): array
    {
        $role = $this->sentinel->getRoleRepository()->findByName($name);

        if (!$role instanceof Roles\RoleInterface) {
            throw RoleNotFoundException::fromName($name);
        }

        return $role->getUsers()->toArray();
    }

    public function create(string $email, string $password, array $data = []): UserInterface
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

    public function activate(string $email)
    {
        $user           = $this->findByLogin($email)->getUser();
        $activationCode = $this->sentinel->getActivationRepository()->create($user)->getCode();

        $this->sentinel->getActivationRepository()->complete($user, $activationCode);
    }

    public function promoteTo(string $email, string $roleName)
    {
        $role = $this->sentinel->getRoleRepository()->findByName(\strtolower($roleName));

        if (!$role instanceof Roles\RoleInterface) {
            throw RoleNotFoundException::fromName($roleName);
        }

        $role
            ->users()
            ->attach($this->findByLogin($email)->getId());
    }

    public function demoteFrom(string $email, string $roleName)
    {
        $role = $this->sentinel->getRoleRepository()->findByName(\strtolower($roleName));

        if (!$role instanceof Roles\RoleInterface) {
            throw RoleNotFoundException::fromName($roleName);
        }

        $role
            ->users()
            ->detach($this->findByLogin($email)->getId());
    }
}
