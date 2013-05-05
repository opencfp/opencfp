<?php

namespace TrueNorth\OpenCFP;


class DatabaseTest extends \PHPUnit_Framework_TestCase {
    protected function setUp()
    {
        putenv(Configuration::OPENCFP_PDO_DSN);
        putenv(Configuration::OPENCFP_PDO_USER);
        putenv(Configuration::OPENCFP_PDO_PASSWORD);
    }

    public function testGetPDO()
    {
        $database = new Database();
        $pdo = $database->getPDO();
        $this->assertInstanceOf(
            'PDO',
            $pdo,
            "getPDO returns a PDO object"
        );
    }
}
