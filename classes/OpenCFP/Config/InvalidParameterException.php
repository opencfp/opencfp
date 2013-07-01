<?php

namespace OpenCFP\Config;


class InvalidParameterException extends \RuntimeException
{
    private $parameter;

    public function __construct($parameter, $message = '', $code = 0, \Exception $previous = null)
    {
        $this->parameter = $parameter;

        parent::__construct($message, $code, $previous);
    }

    public function getParameter()
    {
        return $this->parameter;
    }
}