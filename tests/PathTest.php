<?php

namespace OpenCFP\Test;

use OpenCFP\Environment;
use OpenCFP\Path;

/**
 * @covers \OpenCFP\Path
 */
class PathTest extends BaseTestCase
{
    /**
     * @var Path
     */
    private $path;

    public function setUp()
    {
        parent::setUp();
        $this->path = new Path('/home/folder/base', Environment::testing());
    }

    /**
     * @test
     */
    public function basePathReturnsBasePath()
    {
        $this->assertSame('/home/folder/base', $this->path->basePath());
    }

    /**
     * @test
     */
    public function configPathReturnsConfgiPath()
    {
        $this->assertSame(
            '/home/folder/base/config/testing.yml',
            $this->path->configPath()
        );
    }

    /**
     * @test
     */
    public function uploadPathReturnsUploadPath()
    {
        $this->assertSame(
            '/home/folder/base/web/uploads',
            $this->path->uploadPath()
        );
    }

    /**
     * @test
     */
    public function templatesPathReturnsTemplatesPath()
    {
        $this->assertSame(
            '/home/folder/base/resources/views',
            $this->path->templatesPath()
        );
    }

    /**
     * @test
     */
    public function publicPathReturnsPublicPath()
    {
        $this->assertSame(
            '/home/folder/base/web',
            $this->path->publicPath()
        );
    }

    /**
     * @test
     */
    public function assetsPathReturnsAssetsPath()
    {
        $this->assertSame(
            '/home/folder/base/web/assets',
            $this->path->assetsPath()
        );
    }

    /**
     * @test
     */
    public function cacheTwigPathReturnsCacheTwigPath()
    {
        $this->assertSame(
            '/home/folder/base/cache/twig',
            $this->path->cacheTwigPath()
        );
    }

    /**
     * @test
     */
    public function cachePurifierPathReturnsPurifierPath()
    {
        $this->assertSame(
            '/home/folder/base/cache/htmlpurifier',
            $this->path->cachePurifierPath()
        );
    }
}
