<?php

namespace OpenCFP\Entity;

class UserGroup extends \Spot\Entity
{
    protected static $table = 'users_groups';

    public static function fields()
    {
        return [
            'id' => ['type' => 'integer', 'autoincrement' => true, 'primary' => true],
            'user_id' => ['type' => 'integer'],
            'group_id' => ['type' => 'integer']
        ];
    }
}

