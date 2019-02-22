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

use Symfony\Component\HttpFoundation\RedirectResponse;

interface UserAccess
{
    /**
     * If a user doesn't have access to a page they get redirected, otherwise nothing happens
     *
     * @param Authentication $auth
     * @param string         $role Role to check against
     *
     * @return RedirectResponse|void
     */
    public static function userHasAccess(Authentication $auth, string $role = '');
}
