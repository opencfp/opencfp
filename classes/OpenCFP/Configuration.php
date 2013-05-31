<?php
namespace OpenCFP;

class Configuration
{
    const OPENCFP_PDO_DSN = 'OPENCFP_PDO_DSN';
    const OPENCFP_PDO_USER = 'OPENCFP_PDO_USER';
    const OPENCFP_PDO_PASSWORD = 'OPENCFP_PDO_PASSWORD';
    const OPENCFP_SMTP_HOST = 'OPENCFP_SMTP_HOST';
    const OPENCFP_SMTP_PORT = 'OPENCFP_SMTP_PORT';
    const OPENCFP_SMTP_USER = 'OPENCFP_SMTP_USER';
    const OPENCFP_SMTP_PASSWORD = 'OPENCFP_SMTP_PASSWORD';

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

    public function getSMTPHost()
    {
        $host = getenv(self::OPENCFP_SMTP_HOST);
        return $host ?: '127.0.0.1';
    }

    public function getSMTPPort()
    {
        $port = getenv(self::OPENCFP_SMTP_PORT);
        return $port ?: '25';
    }

    public function getSMTPUser()
    {
        $user = getenv(self::OPENCFP_SMTP_USER);
        return $user;
    }

    public function getSMTPPassword()
    {
        $password = getenv(self::OPENCFP_SMTP_PASSWORD);
        return $password;
    }
}