<?php

namespace OpenCFP\Entity;

class Speaker extends \Spot\Entity
{
    protected static $table = 'speakers';
    protected static $mapper = 'OpenCFP\Entity\Mapper\Speaker';
    public static function fields()
    {
        return [
            'id' => ['type' => 'integer', 'autoincrement' => true, 'primary' => true],
            'user_id' => ['type' => 'integer', 'required' => true],
            'info' => ['type' => 'text'],
            'bio' => ['type' => 'text'],
            'photo_path' => ['type' => 'string', 'length' => 255]
        ];
    }

    public static function relations(\Spot\MapperInterface $mapper, \Spot\EntityInterface $entity)
    {
        return [
            'user' => $mapper->belongsTo($entity, 'OpenCFP\Entity\User', 'user_id')
        ];
    }
}
