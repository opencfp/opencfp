<?php

namespace OpenCFP\Domain\Services;

use Pagerfanta\View\DefaultView;

class Pagination
{
    private $pagerFanta;

    public function __construct($talkList, int $perPage)
    {
        $adapter = new \Pagerfanta\Adapter\ArrayAdapter($talkList);
        $pagerFanta = new \Pagerfanta\Pagerfanta($adapter);
        $pagerFanta->setMaxPerPage($perPage);
        $pagerFanta->getNbResults();
        $this->pagerFanta = $pagerFanta;
    }

    public function setCurrentPage($page)
    {
        $this->pagerFanta->setCurrentPage($page);
    }

    public function createView($routeGenerator)
    {
        $view = new DefaultView();
        return $view->render(
            $this->pagerFanta,
            $routeGenerator,
            ['proximity' => 3]
        );
    }

    public function getCurrentPage()
    {
        return $this->pagerFanta->getCurrentPage();
    }

    public function getFanta()
    {
        return $this->pagerFanta;
    }
}
