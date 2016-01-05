<?php

namespace OpenCFP\Domain\Entity;

use Spot\Entity;

class Talk extends Entity
{
    protected static $table = 'talks';
    protected static $mapper = \OpenCFP\Domain\Entity\Mapper\Talk::class;

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
            'updated_at' => ['type' => 'datetime', 'value' => new \DateTime()],
        ];
    }

    public static function relations(\Spot\MapperInterface $mapper, \Spot\EntityInterface $entity)
    {
        return [
            'speaker' => $mapper->belongsTo($entity, \OpenCFP\Domain\Entity\User::class, 'user_id'),
            'favorites' => $mapper->hasMany($entity, \OpenCFP\Domain\Entity\Favorite::class, 'talk_id'),
            'comments' => $mapper->hasMany($entity, \OpenCFP\Domain\Entity\TalkComment::class, 'talk_id')
                ->order(['created' => 'ASC']),
            'meta' => $mapper->hasMany($entity, \OpenCFP\Domain\Entity\TalkMeta::class, 'talk_id'),
        ];
    }

    public function toArrayForApi()
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'type' => $this->type,
            'level' => $this->level,
            'category' => $this->category,
            'desired' => $this->desired,
            'slides' => $this->slides,
            'other' => $this->other,
            'sponsor' => $this->sponsor,
        ];
    }
}
