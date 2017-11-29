<?php

declare(strict_types=1);

/**
 * Copyright (c) 2013-2017 OpenCFP
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @see https://github.com/opencfp/opencfp
 */

namespace OpenCFP\Test\Unit;

use OpenCFP\Environment;
use OpenCFP\Path;

/**
 * @covers \OpenCFP\Path
 */
final class PathTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @test
     */
    public function basePathReturnsBasePath()
    {
        $path = new Path('/home/folder/base', Environment::testing());
        $this->assertSame('/home/folder/base', $path->basePath());
    }

    /**
     * @test
     */
    public function configPathReturnsConfgiPath()
    {
        $path = new Path('/home/folder/base', Environment::testing());
        $this->assertSame(
            '/home/folder/base/config/testing.yml',
            $path->configPath()
        );
    }

    /**
     * @test
     */
    public function configPathReturnsProductionConfigPath()
    {
        $prod = new Path('/home/folder/base', Environment::production());
        $this->assertSame(
            '/home/folder/base/config/production.yml',
            $prod->configPath()
        );
    }

    /**
     * @test
     */
    public function configPathReturnsDevelopmentConfigPath()
    {
        $dev = new Path('/home/folder/base', Environment::development());
        $this->assertSame(
            '/home/folder/base/config/development.yml',
            $dev->configPath()
        );
    }

    /**
     * @test
     */
    public function uploadPathReturnsUploadPath()
    {
        $path = new Path('/home/folder/base', Environment::testing());
        $this->assertSame(
            '/home/folder/base/web/uploads',
            $path->uploadPath()
        );
    }

    /**
     * @test
     */
    public function templatesPathReturnsTemplatesPath()
    {
        $path = new Path('/home/folder/base', Environment::testing());
        $this->assertSame(
            '/home/folder/base/resources/views',
            $path->templatesPath()
        );
    }

    /**
     * @test
     */
    public function publicPathReturnsPublicPath()
    {
        $path = new Path('/home/folder/base', Environment::testing());
        $this->assertSame(
            '/home/folder/base/web',
            $path->publicPath()
        );
    }

    /**
     * @test
     */
    public function assetsPathReturnsAssetsPath()
    {
        $path = new Path('/home/folder/base', Environment::testing());
        $this->assertSame(
            '/home/folder/base/web/assets',
            $path->assetsPath()
        );
    }

    /**
     * @test
     */
    public function cacheTwigPathReturnsCacheTwigPath()
    {
        $path = new Path('/home/folder/base', Environment::testing());
        $this->assertSame(
            '/home/folder/base/cache/twig',
            $path->cacheTwigPath()
        );
    }

    /**
     * @test
     */
    public function cachePurifierPathReturnsPurifierPath()
    {
        $path = new Path('/home/folder/base', Environment::testing());
        $this->assertSame(
            '/home/folder/base/cache/htmlpurifier',
            $path->cachePurifierPath()
        );
    }
}
