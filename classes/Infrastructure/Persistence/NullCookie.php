<?php

namespace OpenCFP\Infrastructure\Persistence;

use Cartalyst\Sentinel\Cookies\CookieInterface;

/**
 * This class is used to trick Sentinel.
 * It requires us to have a cookie handler, but since we don't need one
 */
class NullCookie implements CookieInterface
{
    /**
     * Put a value in the Sentinel cookie (to be stored until it's cleared).
     *
     * @param mixed $value
     */
    public function put($value)
    {
    }

    /**
     * Returns the Sentinel cookie value.
     *
     * @return mixed
     */
    public function get()
    {
        return null;
    }

    /**
     * Remove the Sentinel cookie.
     */
    public function forget()
    {
    }
}
