<?php

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
