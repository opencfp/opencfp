<?php

namespace OpenCFP;

trait ContainerAware
{
    /**
     * @var Application
     */
    protected $app;

    public function setApplication(Application $application)
    {
        $this->app = $application;
    }

    protected function service(string $slug)
    {
        return $this->app[$slug];
    }
}
