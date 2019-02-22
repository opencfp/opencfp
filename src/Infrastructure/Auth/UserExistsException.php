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

class UserExistsException extends \UnexpectedValueException
{
    public static function fromEmail(string $email): self
    {
        return new self(\sprintf(
            'A user with the email address "%s" already exists.',
            $email
        ));
    }
}
