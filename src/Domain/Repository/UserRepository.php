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

namespace OpenCFP\Domain\Repository;

use OpenCFP\Domain\EntityNotFoundException;
use OpenCFP\Domain\Model\User;

interface UserRepository
{
    /**
     * Retrieves a user with associated talks.
     *
     * @param int $id
     *
     * @throws EntityNotFoundException
     *
     * @return User the speaker that matches given identifier
     */
    public function findById(int $id): User;

    /**
     * Saves a speaker and their talks.
     *
     * @param User $user
     */
    public function persist(User $user);
}
