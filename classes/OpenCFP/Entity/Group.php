<?php

namespace OpenCFP\Entity;

class Group extends \Spot\Entity
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

    public static function relations(\Spot\MapperInterface $mapper, \Spot\EntityInterface $entity)
    {
        return [
            'users' => $mapper->hasManyThrough($entity, '\OpenCFP\Entity\User', '\OpenCFP\Entity\UserGroup', 'user_id', 'group_id'),
        ];
    }
}
