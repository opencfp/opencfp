<?php

declare(strict_types=1);

/**
 * Copyright (c) 2013-2019 OpenCFP
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @see https://github.com/opencfp/opencfp
 */

namespace OpenCFP\Test\Unit\Domain\Services;

use OpenCFP\Domain\Services\Pagination;
use Pagerfanta\Pagerfanta;
use PHPUnit\Framework;

final class PaginationTest extends Framework\TestCase
{
    /**
     * @var Pagination
     */
    private $pagination;

    protected function setUp()
    {
        $this->pagination = new Pagination([1, 2, 3, 4, 5, 6, 7, 8, 9, 10], 2);
    }

    /**
     * @test
     */
    public function currentPageGetterAndSetterWork()
    {
        $this->pagination->setCurrentPage(null);
        $this->assertSame(1, $this->pagination->getCurrentPage());
        $this->pagination->setCurrentPage('');
        $this->assertSame(1, $this->pagination->getCurrentPage());
        $this->pagination->setCurrentPage(2);
        $this->assertSame(2, $this->pagination->getCurrentPage());
    }

    /**
     * @test
     */
    public function getFantaWorks()
    {
        $this->assertInstanceOf(Pagerfanta::class, $this->pagination->getFanta());
    }

    /**
     * @test
     */
    public function createViewWorks()
    {
        $view = $this->pagination->createView('/help/example?');
        $this->assertContains('/help/example?', $view);
        $this->assertContains('page=2', $view);

        $otherView = $this->pagination->createView('/help/example?', ['filter' => 'bam']);
        $this->assertContains('/help/example?', $otherView);
        $this->assertContains('page=2', $otherView);
        $this->assertContains('filter=bam', $otherView);
    }
}
