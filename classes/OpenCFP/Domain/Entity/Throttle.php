<?php

namespace OpenCFP\Domain\Entity;

use Spot\Entity;

class Throttle extends Entity
{
    protected static $table = 'throttle';

    public static function fields()
    {
        return [
            'id' => ['type' => 'integer', 'autoincrement' => true, 'primary' => true],
            'user_id' => ['type' => 'integer'],
            'ip_address' => ['type' => 'string', 'length' => 255, 'value' => null],
            'attempts' => ['type' => 'integer', 'value' => 0],
            'suspended' => ['type' => 'smallint', 'value' => 0],
            'banned' => ['type' => 'smallint', 'value' => 0],
            'last_attempt_at' => ['type' => 'time', 'value' => null],
            'suspended_at' => ['type' => 'time', 'value' => null],
            'banned_at' => ['type' => 'time', 'value' => null]
        ];
    }
}

