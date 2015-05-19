<?php

namespace OpenCFP; 

trait ContainerAware
{
    /**
     * @var Application
     */
    private $app;

    final public function setApplication(Application $application)
    {
        $this->app = $application;
    }

    final protected function service($slug)
    {
        return $this->app[$slug];
    }
} 