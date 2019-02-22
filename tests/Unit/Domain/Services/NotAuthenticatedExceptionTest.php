<?php

declare(strict_types=1);

/**
 * Copyright (c) 2013-2019 OpenCFP
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @see https://github.com/opencfp/opencfp
 */

namespace OpenCFP\Test\Unit\Domain\Services;

use Localheinz\Test\Util\Helper;
use OpenCFP\Domain\Services\NotAuthenticatedException;
use PHPUnit\Framework;

final class NotAuthenticatedExceptionTest extends Framework\TestCase
{
    use Helper;

    /**
     * @test
     */
    public function isException()
    {
        $this->assertClassExtends(\Exception::class, NotAuthenticatedException::class);
    }
}
