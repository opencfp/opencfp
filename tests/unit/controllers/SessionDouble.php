<?php

class SessionDouble extends Symfony\Component\HttpFoundation\Session\Session
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
