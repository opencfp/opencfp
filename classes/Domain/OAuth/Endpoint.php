<?php

namespace OpenCFP\Domain\OAuth;

use Spot\Entity;

class Endpoint extends Entity
{
    protected static $table = 'oauth_client_redirect_uris';

    public static function fields()
    {
        return [
            'id' => ['type' => 'string', 'autoincrement' => true, 'primary' => true],
            'client_id' => ['type' => 'string', 'length' => 255, 'required' => true],
            'redirect_uri' => ['type' => 'string', 'length' => 255, 'required' => true],
        ];
    }
}
