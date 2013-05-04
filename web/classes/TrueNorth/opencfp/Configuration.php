<?php
namespace TrueNorth\opencfp;

class Configuration
{
    const OPENCFP_MYSQL_HOST = 'OPENCFP_MYSQL_HOST';

    public function getMySQLHost()
    {
        $host = getenv(self::OPENCFP_MYSQL_HOST);
        return $host ?: 'localhost';
    }
}