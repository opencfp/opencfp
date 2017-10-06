<?php

namespace OpenCFP\Util;

use Exception;

trait Immutable
{
    public function __get($name)
    {
        return $this->{$name};
    }

    public function __set($name, $value)
    {
        throw new Exception(sprintf(
            '%s is immutable.',
            class_basename(get_class($this))
        ));
    }
}
