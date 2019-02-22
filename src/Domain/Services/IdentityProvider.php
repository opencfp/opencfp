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

namespace OpenCFP\Domain\Services;

use OpenCFP\Domain\Model\User;

interface IdentityProvider
{
    /**
     * Retrieves the currently authenticate user's username.
     *
     * @throws NotAuthenticatedException
     *
     * @return User
     */
    public function getCurrentUser();
}
