<?php

namespace OpenCFP\Http\Controller\Admin;

use OpenCFP\Application;

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
        return $this->userHasAccess(null);
    }
}
