<?php

namespace OpenCFP\Tests\Config;

use OpenCFP\Config\ConfigINIFileLoader;
use OpenCFP\Config\ParameterResolver;

class ConfigINIFileLoaderTest extends \PHPUnit_Framework_TestCase
{
    public function testLoad()
    {
        $container = new \Pimple();
        $container['app.dir'] = '/tmp';

        $loader = new ConfigINIFileLoader($container, new ParameterResolver($container));
        $loader->load(__DIR__.'/../Fixtures/config.ini');

        $this->assertSame($container['application.name'], 'Demo');
        $this->assertSame($container['twig.path'], '/tmp/templates');
        $this->assertSame($container['twig.options']['cache'], '/tmp/cache/twig');
    }

    public function testCantLoadMissingIniFile()
    {
        $this->setExpectedException('InvalidArgumentException');

        $container = new \Pimple();
        $loader = new ConfigINIFileLoader($container, new ParameterResolver($container));
        $loader->load(__DIR__.'/missing.ini');
    }

    public function testCantLoadInvalidIniFile()
    {
        $this->setExpectedException('RuntimeException');

        $container = new \Pimple();
        $loader = new ConfigINIFileLoader($container, new ParameterResolver($container));
        $loader->load(__DIR__.'/../Fixtures/invalid.ini');
    }

    /**
     * @dataProvider provideStringValues
     */
    public function testParseValue($value, $expected)
    {
        $this->assertSame($expected, ConfigINIFileLoader::parseValue($value));
    }

    public function provideStringValues()
    {
        return array(
            array(null, null),
            array('null', null),
            array('NULL', null),
            array(true, true),
            array('true', true),
            array('TRUE', true),
            array(false, false),
            array('false', false),
            array('FALSE', false),
            array(10, 10),
            array('10', 10),
            array(-10, -10),
            array('-10', -10),
            array(10.6, 10.6),
            array('10.6', 10.6),
            array(-10.6, -10.6),
            array('-10.6', -10.6),
        );
    }
}