<?php

namespace OpenCFP\Domain\OAuth;

use Spot\Entity;
use Spot\EntityInterface;
use Spot\MapperInterface;

class Client extends Entity
{
    protected static $table = 'oauth_clients';

    public static function fields()
    {
        return [
            'id' => ['type' => 'string', 'autoincrement' => false, 'primary' => true],
            'secret' => ['type' => 'string', 'length' => 255, 'required' => true],
            'name' => ['type' => 'string', 'length' => 255],
        ];
    }

    public static function relations(MapperInterface $mapper, EntityInterface $entity)
    {
        return [
            'endpoints' => $mapper->hasMany($entity, 'OpenCFP\Domain\OAuth\Endpoint', 'client_id')
        ];
    }

    public function toArrayForApi()
    {
        $redirectUris = [];

        foreach ($this->endpoints->execute() as $endpoint) {
            $redirectUris[] = $endpoint->redirect_uri;
        }

        return [
            'id' => $this->id,
            'secret' => $this->secret,
            'name' => $this->name,
            'redirect_uris' => $redirectUris
        ];
    }
}
