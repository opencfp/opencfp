<?php

namespace OpenCFP\Tests\Config;

use OpenCFP\Config\ParameterResolver;

class ParameterResolverTest extends \PHPUnit_Framework_TestCase
{
    public function testResolveUndefinedParameters()
    {
        $this->setExpectedException('OpenCFP\Config\InvalidParameterException');

        $resolver = new ParameterResolver(new \Pimple());
        $resolver->resolve('${app.dir}/cache');
    }

    /**
     * @dataProvider provideInvalidParameters
     */
    public function testCantReplaceNonPrimitivePlaceholders($parameter)
    {
        $this->setExpectedException('OpenCFP\Config\InvalidParameterException');

        $container = new \Pimple();
        $container['foo'] = $parameter;

        $resolver = new ParameterResolver($container);
        $resolver->resolve('${foo}/bar');
    }

    public function provideInvalidParameters()
    {
        return array(
            array(true),
            array(false),
            array(null),
            array(array('foo' => 'bar')),
            array(new \stdClass()),
        );
    }

    /**
     * @dataProvider provideParameters
     */
    public function testResolveParameter($parameter, $resolved)
    {
        $container = new \Pimple();
        $container['app.dir'] = '/tmp';
        $container['app.env'] = 'dev';

        $resolver = new ParameterResolver($container);

        $this->assertSame($resolved, $resolver->resolve($parameter));
    }

    public function provideParameters()
    {
        return array(
            // Safe stripped parameters
            array(42, 42),
            array(42.50, 42.50),
            array(true, true),
            array(false, false),
            array(null, null),
            // No placeholders to replace
            array('/foo', '/foo'),
            // Only 1 placeholder to replace
            array('${app.dir}/cache', '/tmp/cache'),
            // More than 1 placeholder to replace
            array('${app.dir}/cache/${app.env}', '/tmp/cache/dev'),
            // Collection with nested dynamic parameters with placeholders
            array(
                array('size' => 42, 'path' => '${app.dir}/cache/${app.env}'),
                array('size' => 42, 'path' => '/tmp/cache/dev'),
            ),
        );
    }
}