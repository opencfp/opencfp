<?php

declare(strict_types=1);

/**
 * Copyright (c) 2013-2017 OpenCFP
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @see https://github.com/opencfp/opencfp
 */

namespace OpenCFP;

/**
 * @deprecated
 * @see https://qafoo.com/blog/057_containeraware_considered_harmful.html
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
