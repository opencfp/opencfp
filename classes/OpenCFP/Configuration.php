<?php
namespace OpenCFP;

class Configuration
{
    const OPENCFP_PDO_DSN = 'OPENCFP_PDO_DSN';
    const OPENCFP_PDO_USER = 'OPENCFP_PDO_USER';
    const OPENCFP_PDO_PASSWORD = 'OPENCFP_PDO_PASSWORD';

    public function getPDODSN()
    {
        $dsn = getenv(self::OPENCFP_PDO_DSN);
        return $dsn ?: 'sqlite::memory:';
    }

    public function getPDOUser()
    {
        $user = getenv(self::OPENCFP_PDO_USER);
        return $user ?: 'root';
    }

    public function getPDOPassword()
    {
        $password = getenv(self::OPENCFP_PDO_PASSWORD);
        return $password ?: '';
    }
}