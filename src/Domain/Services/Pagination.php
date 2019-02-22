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

namespace OpenCFP\Domain\Services;

use Pagerfanta\Pagerfanta;
use Pagerfanta\View\DefaultView;

class Pagination
{
    /**
     * @var Pagerfanta
     */
    private $pagerFanta;

    public function __construct($talkList, int $perPage = 20)
    {
        $adapter    = new \Pagerfanta\Adapter\ArrayAdapter($talkList);
        $pagerFanta = new Pagerfanta($adapter);
        $pagerFanta->setMaxPerPage($perPage);
        $this->pagerFanta = $pagerFanta;
    }

    public function setCurrentPage($page)
    {
        if ($page !== null && $page !== '') {
            $this->pagerFanta->setCurrentPage($page);
        }
    }

    public function createView(string $baseUrl, $queryParams = []): string
    {
        $routeGenerator = function ($page) use ($queryParams, $baseUrl) {
            $queryParams['page'] = $page;

            return $baseUrl . \http_build_query($queryParams);
        };

        $view = new DefaultView();

        return $view->render(
            $this->pagerFanta,
            $routeGenerator,
            ['proximity' => 3]
        );
    }

    public function getCurrentPage(): int
    {
        return $this->pagerFanta->getCurrentPage();
    }

    public function getFanta(): Pagerfanta
    {
        return $this->pagerFanta;
    }
}
