<?php

namespace TrueNorth\OpenCFP;


class DatabaseTest extends \PHPUnit_Framework_TestCase {

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
