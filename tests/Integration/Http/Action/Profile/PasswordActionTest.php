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

namespace OpenCFP\Test\Integration\Http\Action\Profile;

use OpenCFP\Test\Integration\WebTestCase;

final class PasswordActionTest extends WebTestCase
{
    /**
     * @test
     */
    public function displayChangePasswordWhenAllowed()
    {
        $response = $this
            ->asLoggedInSpeaker()
            ->get('/profile/change_password');

        $this->assertResponseIsSuccessful($response);
        $this->assertResponseBodyContains('Change Your Password', $response);
    }
}
