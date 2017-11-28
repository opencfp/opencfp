<?php

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
