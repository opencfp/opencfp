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

namespace OpenCFP\Test\Helper;

use OpenCFP\Domain\Model\User;
use OpenCFP\Domain\Services\IdentityProvider;

final class MockIdentityProvider implements IdentityProvider
{
    private $wrapped;

    private $currentUser;

    public function __construct(IdentityProvider $wrapped)
    {
        $this->wrapped = $wrapped;
    }

    public function overrideCurrentUser(User $user = null)
    {
        $this->currentUser = $user;
    }

    public function reset()
    {
        $this->currentUser = null;
    }

    public function getCurrentUser(): User
    {
        return $this->currentUser ?: $this->wrapped->getCurrentUser();
    }
}
