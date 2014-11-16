<?php

namespace OpenCFP\Domain\Entity;

use Spot\Entity;

class Favorite extends Entity
{
    protected static $table = 'favorites';

    public static function fields()
    {
        return [
            'id' => ['type' => 'integer', 'autoincrement' => true, 'primary' => true],
            'admin_user_id' => ['type' => 'integer', 'required' => true],
            'talk_id' => ['type' => 'integer', 'required' => true],
            'created' => ['type' => 'datetime', 'value' => new \DateTime()]
        ];
    }

    public static function relations(\Spot\MapperInterface $mapper, \Spot\EntityInterface $entity)
    {
        return [
            'talk' => $mapper->belongsTo($entity, 'OpenCFP\Domain\Entity\Talk', 'talk_id')
        ];
    }
}
