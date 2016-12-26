<?php
namespace OpenCFP\Domain\Entity;


use Spot\Entity;

class TalkTag extends Entity
{
    protected static $table = 'talks_tags';

    public static function fields()
    {
        return [
            'id'        => ['type' => 'integer', 'primary' => true, 'autoincrement' => true],
            'talk_id' => ['type' => 'integer', 'required' => true, 'unique' => 'talk_tag'],
            'tag_id' => ['type' => 'integer', 'required' => true, 'unique' => 'talk_tag']
        ];
    }

    public static function relations(\Spot\MapperInterface $mapper, \Spot\EntityInterface $entity)
    {
        return [
            'talk' => $mapper->belongsTo($entity, \OpenCFP\Domain\Entity\Talk::class, 'talk_id'),
            'tag'  => $mapper->belongsTo($entity, \OpenCFP\Domain\Entity\Tag::class, 'tag_id')
        ];
    }
}
