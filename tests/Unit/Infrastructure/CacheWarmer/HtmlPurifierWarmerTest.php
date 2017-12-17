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

namespace OpenCFP\Test\Unit\Infrastructure\CacheWarmer;

use Localheinz\Test\Util\Helper;
use OpenCFP\Infrastructure\CacheWarmer\HtmlPurifierWarmer;
use PHPUnit\Framework;
use Symfony\Component\Filesystem;
use Symfony\Component\HttpKernel;

final class HtmlPurifierWarmerTest extends Framework\TestCase
{
    use Helper;

    public function testIsFinal()
    {
        $this->assertClassIsFinal(HtmlPurifierWarmer::class);
    }

    public function testImplementsCacheWarmerInterface()
    {
        $this->assertClassImplementsInterface(HttpKernel\CacheWarmer\CacheWarmerInterface::class, HtmlPurifierWarmer::class);
    }

    public function testIsRequired()
    {
        $filesystem = $this->createFilesystemMock();

        $cacheWarmer = new HtmlPurifierWarmer($filesystem);

        $this->assertFalse($cacheWarmer->isOptional());
    }

    public function testWarmUpCreatesCacheDirectory()
    {
        $cacheDirectory = $this->faker()->slug;

        $filesystem = $this->createFilesystemMock();

        $filesystem
            ->expects($this->once())
            ->method('mkdir')
            ->with(
                $this->identicalTo($cacheDirectory . '/htmlpurifier'),
                $this->identicalTo(0755)
            );

        $cacheWarmer = new HtmlPurifierWarmer($filesystem);

        $cacheWarmer->warmUp($cacheDirectory);
    }

    /**
     * @return Filesystem\Filesystem|Framework\MockObject\MockObject
     */
    private function createFilesystemMock(): Filesystem\Filesystem
    {
        return $this->createMock(Filesystem\Filesystem::class);
    }
}
