<?php

namespace OpenCFP\Entity;

class Phinxlog extends \Spot\Entity
{
    protected static $table = 'phinxlog';

    public static function fields()
    {
        return [
            'version' => ['type' => 'bigint', 'required' => true],
            'start_time' => ['type' => 'time', 'value' => time()],
            'end_time' => ['type' => 'time', 'value' => null]
        ];
    }
}
