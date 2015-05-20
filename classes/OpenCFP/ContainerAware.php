<?php

namespace OpenCFP; 

trait ContainerAware
{
    /**
     * @var Application
     */
    private $app;

    public function setApplication(Application $application)
    {
        $this->app = $application;
    }

    protected function service($slug)
    {
        return $this->app[$slug];
    }
} 