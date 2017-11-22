<?php

namespace OpenCFP\Infrastructure\Auth;

final class UserNotFoundException extends \RuntimeException
{
    public static function userNotFound(string $userInfo): self
    {
        return new self(sprintf('Unable to find a user matching %s', $userInfo));
    }
}
