<?php

declare(strict_types=1);

/**
 * Copyright (c) 2013-2017 OpenCFP
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @see https://github.com/opencfp/opencfp
 */

namespace OpenCFP\Domain\Services;

use OpenCFP\Infrastructure\Auth\UserInterface;

interface AccountManagement
{
    public function findById(int $userId): UserInterface;

    public function findByLogin(string $email): UserInterface;

    public function findByRole(string $role): array;

    public function create(string $email, string $password, array $data = []): UserInterface;

    public function activate(string $email);

    public function promoteTo(string $email, string $role);

    public function demoteFrom(string $email, string $role);
}
