<?php

/**
 * Copyright (c) 2013-2017 OpenCFP
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @see https://github.com/opencfp/opencfp
 */

namespace OpenCFP\Http\Controller;

use Silex\Application;

trait FlashableTrait
{
    /**
     * Get Session Flash Message
     *
     * @param Application $app OpenCFP Application
     *
     * @return array
     */
    public function getFlash(Application $app)
    {
        $flash = $app['session']->get('flash');
        $this->clearFlash($app);

        return $flash;
    }

    /**
     * Clear Session Flash Message
     *
     * @param Application $app OpenCFP Application
     */
    public function clearFlash(Application $app)
    {
        $app['session']->set('flash', null);
    }
}
