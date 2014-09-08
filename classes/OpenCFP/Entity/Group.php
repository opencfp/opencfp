<?php

namespace OpenCFP\Entity;

class Favorite extends \Spot\Entity
{
    protected static $table = 'groups';

    public static function fields()
    {
        return [
            'id' => ['type' => 'integer', 'autoincrement' => true, 'primary' => true],
            'name' => ['type' => 'string', 'length' => 255, 'required' => true],
            'permissions' => ['type' => 'text'],
            'created_at' => ['type' => 'datetime', 'value' => new \DateTime()],
            'updated_at' => ['type' => 'datetime', 'value' => new \DateTime()]
        ];
    }
}
