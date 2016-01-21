<?php

namespace OpenCFP\Test\Http\Controller;

use Symfony\Component\HttpFoundation\Session\Session;

class SessionDouble extends Session
{
    protected $flash;

    public function get($value, $default = null)
    {
        return $this->$value;
    }

    public function set($name, $value)
    {
        $this->$name = $value;
    }
}
