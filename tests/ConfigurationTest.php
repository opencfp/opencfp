<?php

namespace TrueNorth\OpenCFP;

require_once '../vendor/autoload.php';

class ConfigurationTest extends \PHPUnit_Framework_TestCase {

    public function testGetPDODSNDefault()
    {
        putenv(Configuration::OPENCFP_PDO_DSN);
        $configuration = new Configuration();
        $dsn = $configuration->getPDODSN();
        $this->assertEquals(
            "sqlite::memory:",
            $dsn,
            "The default PDO DSN is in-memory sqlite"
        );
    }

    public function testGetPDODSNFromEnvironment()
    {
        $expected = 'mysql:dbname=cfp;host=localhost';
        putenv(Configuration::OPENCFP_PDO_DSN . '=' . $expected);
        $configuration = new Configuration();
        $dsn = $configuration->getPDODSN();
        $this->assertEquals(
            $expected,
            $dsn,
            "The PDO DSN is " . $expected
        );
    }

    public function testGetPDOUserDefault()
    {
        putenv(Configuration::OPENCFP_PDO_USER);
        $configuration = new Configuration();
        $user = $configuration->getPDOUser();
        $this->assertEquals(
            "root",
            $user,
            "The default MySQL user is root"
        );
    }

    public function testGetPDOUserFromEnvironment()
    {
        $expected = 'testUserName';
        putenv(Configuration::OPENCFP_PDO_USER . '=' . $expected);
        $configuration = new Configuration();
        $user = $configuration->getPDOUser();
        $this->assertEquals(
            $expected,
            $user,
            "The MySQL user is " . $expected
        );
    }

    public function testGetPDOPasswordDefault()
    {
        putenv(Configuration::OPENCFP_PDO_PASSWORD);
        $configuration = new Configuration();
        $password = $configuration->getPDOPassword();
        $this->assertEquals(
            "",
            $password,
            "The default MySQL password is blank"
        );
    }

    public function testGetPDOPasswordFromEnvironment()
    {
        $expected = 'testPassword';
        putenv(Configuration::OPENCFP_PDO_PASSWORD . '=' . $expected);
        $configuration = new Configuration();
        $password = $configuration->getPDOPassword();
        $this->assertEquals(
            $expected,
            $password,
            "The MySQL user is " . $expected
        );
    }
}
