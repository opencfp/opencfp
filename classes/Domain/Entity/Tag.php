<?php
namespace OpenCFP\Domain\Entity;

use Spot\Entity;
use Spot\EntityInterface;
use Spot\MapperInterface;

class Tag extends Entity
{
    protected static $table = 'tags';

    public static function fields()
    {
        return [
            'id' => ['type' => 'integer', 'autoincrement' => true, 'primary' => true],
            'tag' => ['type' => 'string', 'length' => 50, 'required' => true, 'unique' => 'tag'],
        ];
    }

    public static function relations(MapperInterface $mapper, EntityInterface $entity)
    {
        return [
            'talks' => $mapper->hasManyThrough($entity, Talk::class, TalkTag::class, 'tag_id', 'talk_id'),
        ];
    }
}
