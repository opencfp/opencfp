<?php
namespace TrueNorth\OpenCFP;

class Database
{
    function __construct()
    {
        putenv(Configuration::OPENCFP_PDO_DSN);
        putenv(Configuration::OPENCFP_PDO_USER);
        putenv(Configuration::OPENCFP_PDO_PASSWORD);
    }

    public function getPDO()
    {
        $configuration = new Configuration();
        return new \PDO(
            $configuration->getPDODSN(),
            $configuration->getPDOUser(),
            $configuration->getPDOPassword()
        );
    }
}