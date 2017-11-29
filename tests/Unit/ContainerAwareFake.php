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

namespace OpenCFP\Test\Unit;

use OpenCFP\ContainerAware;

class ContainerAwareFake
{
    use ContainerAware;

    /**
     * @param string $slug
     *
     * @return mixed
     */
    public function getService($slug)
    {
        return $this->service($slug);
    }
}
