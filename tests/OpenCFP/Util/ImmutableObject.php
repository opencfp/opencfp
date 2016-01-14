<?php

namespace OpenCFP\Test\Util;

use OpenCFP\Util\Immutable;

class ImmutableObject
{
    use Immutable;

    private $value;

    public function __construct($value = 'Foo')
    {
        $this->value = $value;
    }
}