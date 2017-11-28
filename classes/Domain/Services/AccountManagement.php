<?php

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
    public function findById($userId): UserInterface;

    public function findByLogin($email): UserInterface;

    public function findByRole($role): array;

    public function create($email, $password, array $data = []): UserInterface;

    public function activate($email);

    public function promoteTo($email, $role);

    public function demoteFrom($email, $role);
}
