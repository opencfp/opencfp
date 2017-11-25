<?php

namespace OpenCFP;

/**
 * @deprecated
 * @link https://qafoo.com/blog/057_containeraware_considered_harmful.html
 */
trait ContainerAware
{
    /**
     * @var Application
     */
    protected $app;

    /**
     * @deprecated https://qafoo.com/blog/057_containeraware_considered_harmful.html
     *
     * @param Application $application
     */
    public function setApplication(Application $application)
    {
        $this->app = $application;
    }

    /**
     * @deprecated
     *
     * @param string $slug
     *
     * @return mixed
     */
    protected function service(string $slug)
    {
        return $this->app[$slug];
    }
}
