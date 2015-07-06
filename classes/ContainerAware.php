<?php

namespace OpenCFP;

trait ContainerAware
{
    /**
     * @var \Silex\Application
     */
    public $app;

    public function setApplication(Application $application)
    {
        $this->app = $application;
    }

    protected function service($slug)
    {
        return $this->app[$slug];
    }
}
