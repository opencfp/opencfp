<?php
namespace OpenCFP;

class Database
{
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