<?php

namespace OpenCFP\Domain\Entity;

class TalkComment extends \Spot\Entity
{
    protected static $table = 'talk_comments';

    public static function fields()
    {
        return [
            'id' => ['type' => 'integer', 'autoincrement' => true, 'primary' => true],
            'user_id' => ['type' => 'integer', 'required' => true],
            'talk_id' => ['type' => 'integer', 'required' => true],
            'message' => ['type' => 'text', 'required' => true],
            'created' => ['type' => 'datetime', 'value' => new \DateTime()],
        ];
    }

    public static function relations(\Spot\MapperInterface $mapper, \Spot\EntityInterface $entity)
    {
        return [
            'talk' => $mapper->belongsTo($entity, 'OpenCFP\Domain\Entity\Talk', 'talk_id'),
            'user' => $mapper->belongsTo($entity, 'OpenCFP\Domain\Entity\User', 'user_id'),
        ];
    }
}
