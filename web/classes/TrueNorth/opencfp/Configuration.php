<?php
namespace TrueNorth\opencfp;

class Configuration
{
    const OPENCFP_MYSQL_HOST = 'OPENCFP_MYSQL_HOST';
    const OPENCFP_MYSQL_DATABASE_NAME = 'OPENCFP_MYSQL_DATABASE_NAME';
    const OPENCFP_MYSQL_USER_NAME = 'OPENCFP_MYSQL_USER_NAME';
    const OPENCFP_MYSQL_PASSWORD = 'OPENCFP_MYSQL_PASSWORD';

    public function getMySQLHost()
    {
        $host = getenv(self::OPENCFP_MYSQL_HOST);
        return $host ?: 'localhost';
    }

    public function getMySQLDatabaseName()
    {
        $databaseName = getenv(self::OPENCFP_MYSQL_DATABASE_NAME);
        return $databaseName ?: 'cfp';
    }

    public function getMySQLUser()
    {
        $user = getenv(self::OPENCFP_MYSQL_USER_NAME);
        return $user ?: 'root';
    }

    public function getMySQLPassword()
    {
        $password = getenv(self::OPENCFP_MYSQL_PASSWORD);
        return $password ?: '';
    }
}