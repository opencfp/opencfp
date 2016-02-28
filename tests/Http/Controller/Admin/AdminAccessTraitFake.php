<?php

namespace OpenCFP\Test\Http\Controller\Admin;

use OpenCFP\Application;
use OpenCFP\Http\Controller\Admin\AdminAccessTrait;

class AdminAccessTraitFake
{
    use AdminAccessTrait;

    /**
     * @var Application
     */
    private $app;

    public function __construct(Application $application)
    {
        $this->app = $application;
    }

    /**
     * @return bool
     */
    public function hasAdminAccess()
    {
        return $this->userHasAccess();
    }
}
