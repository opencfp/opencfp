<?php

namespace TrueNorth\opencfp;

require_once 'web/vendor/autoload.php';

class ConfigurationTest extends \PHPUnit_Framework_TestCase {

    public function testGetMySQLHostDefault()
    {
        putenv('OPENCFP_MYSQL_HOST');
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
}
