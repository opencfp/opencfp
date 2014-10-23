<?php
/**
 * Created by PhpStorm.
 * User: kayladnls
 * Date: 10/16/14
 * Time: 10:54 PM
 */

namespace OpenCFP\Controller;

use Silex\Application;;


trait FlashableTrait
{
    public function getFlash(Application $app)
    {
        $flasg = $app['session']->get('flash');
        $this->clearFlash($app);

        return $flash;
    }

    public function clearFlash(Application $app)
    {
        $app['session']->set('flash', null);
    }
} 