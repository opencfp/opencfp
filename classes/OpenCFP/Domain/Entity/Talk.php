<?php

namespace OpenCFP\Entity;

class Talk extends \Spot\Entity
{
    protected static $table = 'talks';
    protected static $mapper = 'OpenCFP\Entity\Mapper\Talk';

    public static function fields()
    {
        return [
            'id' => ['type' => 'integer', 'autoincrement' => true, 'primary' => true],
            'title' => ['type' => 'string', 'length' => 100, 'required' => true],
            'description' => ['type' => 'text'],
            'type' => ['type' => 'string', 'length' => 50],
            'user_id' => ['type' => 'integer', 'required' => true],
            'level' => ['type' => 'string', 'length' => 50],
            'category' => ['type' => 'string', 'length' => 50],
            'desired' => ['type' => 'smallint', 'value' => 0],
            'slides' => ['type' => 'string', 'length' => 255],
            'other' => ['type' => 'text'],
            'sponsor' => ['type' => 'smallint', 'value' => 0],
            'selected' => ['type' => 'smallint', 'value' => 0],
            'created_at' => ['type' => 'datetime', 'value' => new \DateTime()],
            'updated_at' => ['type' => 'datetime', 'value' => new \DateTime()]
        ];
    }

    public static function relations(\Spot\MapperInterface $mapper, \Spot\EntityInterface $entity)
    {
        return [
            'speaker' => $mapper->belongsTo($entity, 'OpenCFP\Entity\User', 'user_id'),
            'favorites' => $mapper->hasMany($entity, 'OpenCFP\Entity\Favorite', 'talk_id'),
        ];
    }
}
