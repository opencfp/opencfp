<?php

namespace OpenCFP\Entity;

class User extends \Spot\Entity
{
    protected static $table = 'users';

    public static function fields()
    {
        return [
            'id' => ['type' => 'integer', 'autoincrement' => true, 'primary' => true],
            'email' => ['type' => 'string', 'length' => 255, 'required' => true],
            'password' => ['type' => 'string', 'length' => 255, 'required' => true],
            'permissions' => ['type' => 'text'],
            'activated' => ['type' => 'smallint', 'value' => 0],
            'activation_code' => ['type' => 'string', 'length' => 255],
            'activated_at' => ['type' => 'datetime'],
            'last_login' => ['type' => 'string', 'length' => 255],
            'persist_code' => ['type' => 'string', 'length' => 255],
            'reset_password_code' => ['type' => 'string', 'length' => 255],
            'first_name' => ['type' => 'string', 'length' => 255],
            'last_name' => ['type' => 'string', 'length' => 255],
            'created_at' => ['type' => 'datetime', 'value' => new \DateTime()],
            'updated_at' => ['type' => 'datetime', 'value' => new \DateTime()],
            'company' => ['type' => 'string', 'length' => 255],
            'twitter' => ['type' => 'string', 'length' => 255],
            'airport' => ['type' => 'string', 'length' => 5],
            'hotel' => ['type' => 'smallint', 'value' => 0],
            'transportation' => ['type' => 'smallint', 'value' => 0],
            'info' => ['type' => 'text'],
            'bio' => ['type' => 'text'],
            'photo_path' => ['type' => 'string', 'length' => 255]
        ];
    }

    public static function relations(\Spot\MapperInterface $mapper, \Spot\EntityInterface $entity)
    {
        return [
            'talks' => $mapper->hasMany($entity, 'OpenCFP\Entity\Talk', 'user_id'),
            'groups' => $mapper->hasManyThrough($entity, '\OpenCFP\Entity\Group', '\OpenCFP\Entity\UserGroup', 'group_id', 'user_id'),
        ];
    }
}
