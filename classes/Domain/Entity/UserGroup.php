<?php

namespace OpenCFP\Domain\Entity;

use Spot\Entity;

class UserGroup extends Entity
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
