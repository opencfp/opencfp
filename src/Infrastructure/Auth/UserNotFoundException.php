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

final class UserNotFoundException extends \RuntimeException
{
    public static function fromEmail(string $email): self
    {
        return new self(\sprintf(
            'Unable to find a user with email "%s".',
            $email
        ));
    }

    public static function fromId(int $id): self
    {
        return new self(\sprintf(
            'Unable to find a user with id "%d".',
            $id
        ));
    }
}
