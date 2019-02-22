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

namespace OpenCFP\Test\Unit\Infrastructure\Auth;

use Mockery;
use OpenCFP\Domain\Services\Authentication;
use OpenCFP\Infrastructure\Auth\SpeakerAccess;
use Symfony\Component\HttpFoundation\RedirectResponse;

final class SpeakerAccessTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @test
     */
    public function returnsRedirectResponseIfCheckFailed()
    {
        $auth = Mockery::mock(Authentication::class);
        $auth->shouldReceive('isAuthenticated')->andReturn(false);

        $this->assertInstanceOf(RedirectResponse::class, SpeakerAccess::userHasAccess($auth));
    }

    /**
     * @test
     */
    public function returnsNothingIfCheckSucceeded()
    {
        $auth = Mockery::mock(Authentication::class);
        $auth->shouldReceive('isAuthenticated')->andReturn(true);

        $this->assertNull(SpeakerAccess::userHasAccess($auth));
    }
}
