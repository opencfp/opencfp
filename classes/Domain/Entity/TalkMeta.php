<?php

namespace OpenCFP\Domain\Entity;

class TalkMeta extends \Spot\Entity
{
    protected static $table = 'talk_meta';

    public static function fields()
    {
        return [
            'id' => ['type' => 'integer', 'autoincrement' => true, 'primary' => true],
            'admin_user_id' => ['type' => 'integer', 'required' => true],
            'talk_id' => ['type' => 'integer', 'required' => true],
            'rating' => ['type' => 'smallint', 'default' => 0],
            'viewed' => ['type' => 'boolean', 'default' => false],
            'created' => ['type' => 'datetime', 'value' => new \DateTime()],
        ];
    }

    public static function relations(\Spot\MapperInterface $mapper, \Spot\EntityInterface $entity)
    {
        return [
            'talk' => $mapper->belongsTo($entity, 'OpenCFP\Domain\Entity\Talk', 'talk_id'),
        ];
    }
}
