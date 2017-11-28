<?php

/**
 * Copyright (c) 2013-2017 OpenCFP
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @see https://github.com/opencfp/opencfp
 */

namespace OpenCFP\Test\Unit\Infrastructure\Auth;

use Cartalyst\Sentry\Sentry;
use Cartalyst\Sentry\Users\UserExistsException as SentryUserExsistsException;
use Mockery;
use OpenCFP\Infrastructure\Auth\SentryAccountManagement;
use OpenCFP\Infrastructure\Auth\UserExistsException;

/**
 * @covers \OpenCFP\Infrastructure\Auth\SentryAccountManagement
 */
class SentryAccountManagementTest extends \PHPUnit\Framework\TestCase
{
    public function testCreateThrowsCorrectError()
    {
        $sentryMock = Mockery::mock(Sentry::class);
        $sentryMock->shouldReceive('createUser')->andthrow(new SentryUserExsistsException());
        $accounts = new SentryAccountManagement($sentryMock);
        $this->expectException(UserExistsException::class);
        $accounts->create('mail@mail.mail', 'pass');
    }
}
