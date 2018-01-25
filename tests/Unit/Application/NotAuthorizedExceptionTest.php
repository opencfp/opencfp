<?php

declare(strict_types=1);

/**
 * Copyright (c) 2013-2018 OpenCFP
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @see https://github.com/opencfp/opencfp
 */

namespace OpenCFP\Test\Unit\Application;

use Localheinz\Test\Util\Helper;
use OpenCFP\Application\NotAuthorizedException;
use PHPUnit\Framework;

final class NotAuthorizedExceptionTest extends Framework\TestCase
{
    use Helper;

    public function testIsException()
    {
        $this->assertClassExtends(\Exception::class, NotAuthorizedException::class);
    }
}
