<?php

namespace OpenCFP\Domain\Entity;

use Spot\Entity;

class Group extends Entity
{
    protected static $table = 'groups';

    public static function fields()
    {
        return [
            'id' => ['type' => 'integer', 'autoincrement' => true, 'primary' => true],
            'name' => ['type' => 'string', 'length' => 255, 'required' => true],
            'permissions' => ['type' => 'text'],
            'created_at' => ['type' => 'datetime', 'value' => new \DateTime()],
            'updated_at' => ['type' => 'datetime', 'value' => new \DateTime()],
        ];
    }

    public static function relations(\Spot\MapperInterface $mapper, \Spot\EntityInterface $entity)
    {
        return [
            'users' => $mapper->hasManyThrough($entity, \OpenCFP\Domain\Entity\User::class, \OpenCFP\Domain\Entity\UserGroup::class, 'user_id', 'group_id'),
        ];
    }
}
