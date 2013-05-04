<?php

namespace TrueNorth\opencfp;

require_once 'web/vendor/autoload.php';

class ConfigurationTest extends \PHPUnit_Framework_TestCase {

    public function testGetMySQLHostDefault()
    {
        putenv(Configuration::OPENCFP_MYSQL_HOST);
        $configuration = new Configuration();
        $host = $configuration->getMySQLHost();
        $this->assertEquals(
            "localhost",
            $host,
            "The default MySQL host is localhost"
        );
    }

    public function testGetMySQLHostFromEnvironment()
    {
        $expected = 'test.example.com';
        putenv(Configuration::OPENCFP_MYSQL_HOST . '=' . $expected);
        $configuration = new Configuration();
        $host = $configuration->getMySQLHost();
        $this->assertEquals(
            $expected,
            $host,
            "The MySQL host is " . $expected
        );
    }

    public function testGetMySQLDatabaseNameDefault()
    {
        putenv(Configuration::OPENCFP_MYSQL_DATABASE_NAME);
        $configuration = new Configuration();
        $host = $configuration->getMySQLDatabaseName();
        $this->assertEquals(
            "cfp",
            $host,
            "The default MySQL database name is cfp"
        );
    }

    public function testGetMySQLDatabaseNameFromEnvironment()
    {
        $expected = 'testDatabaseName';
        putenv(Configuration::OPENCFP_MYSQL_DATABASE_NAME . '=' . $expected);
        $configuration = new Configuration();
        $host = $configuration->getMySQLDatabaseName();
        $this->assertEquals(
            $expected,
            $host,
            "The MySQL database name is " . $expected
        );
    }

    public function testGetMySQLUserDefault()
    {
        putenv(Configuration::OPENCFP_MYSQL_USER_NAME);
        $configuration = new Configuration();
        $host = $configuration->getMySQLUser();
        $this->assertEquals(
            "root",
            $host,
            "The default MySQL user is root"
        );
    }

    public function testGetMySQLUserFromEnvironment()
    {
        $expected = 'testUserName';
        putenv(Configuration::OPENCFP_MYSQL_USER_NAME . '=' . $expected);
        $configuration = new Configuration();
        $host = $configuration->getMySQLUser();
        $this->assertEquals(
            $expected,
            $host,
            "The MySQL user is " . $expected
        );
    }
}
