<?php
namespace OpenCFP;
use \Mockery as m;

class ConfigurationTest extends \PHPUnit_Framework_TestCase
{
    protected $configuration;
    protected $parser;
    protected $defaults;

    public function setUp()
    {
        $this->parser = m::mock('OpenCFP\ConfigLoaderInterface');
        $this->defaults = array(
            'database' => array(
                'dsn' => 'something',
                'user' => 'me',
                'password' => 'mipass'
            ),
            'application' => array(
                'title' => 'some title',
                'url' => 'some url'
            ),
            'twig' => array(
                'template_dir' => 'some dir'
            )
        );
    }

    public function tearDown()
    {
        m::close();
    }

    public function testGetPDODSN()
    {
        $expected = 'mysql:dbname=cfp;host=localhost';
        $data = $this->defaults;
        $data['database']['dsn'] = $expected;
        $this->parser->shouldReceive('load')->andReturn($data);
        $this->configuration = new Configuration($this->parser);

        $dsn = $this->configuration->getPDODSN();
        $this->assertEquals(
            $expected,
            $dsn,
            "The DSN is " . $expected
        );
    }

    public function testGetPDOUser()
    {
        $expected = 'testUserName';
        $data = $this->defaults;
        $data['database']['user'] = $expected;
        $this->parser->shouldReceive('load')->andReturn($data);
        $configuration = new Configuration($this->parser);
        $user = $configuration->getPDOUser();
        $this->assertEquals(
            $expected,
            $user,
            "The MySQL user is " . $expected
        );
    }

    public function testGetPDOPassword()
    {
        $expected = 'testPassword';
        $data = $this->defaults;
        $data['database']['password'] = $expected;
        $this->parser->shouldReceive('load')->andReturn($data);
        $configuration = new Configuration($this->parser);
        $password = $configuration->getPDOPassword();
        $this->assertEquals(
            $expected,
            $password,
            "The MySQL password is " . $expected
        );
    }

    public function testGetTwigTemplateDir()
    {
        $expected = 'somedirectory';
        $data = $this->defaults;
        $data['twig']['template_dir'] = $expected;
        $this->parser->shouldReceive('load')->andReturn($data);
        $configuration = new Configuration($this->parser);
        $password = $configuration->getTwigTemplateDir();
        $this->assertEquals(
            $expected,
            $password,
            "The Twig Template Directory is " . $expected
        );
    }
}
