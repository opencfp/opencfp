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

namespace OpenCFP\Test\Integration\Http\Action\Forgot;

use OpenCFP\Test\Integration\WebTestCase;

final class UpdatePasswordActionTest extends WebTestCase
{
    /**
     * @test
     */
    public function rendersResetPasswordFormIfFormIsInvalid()
    {
        $response = $this->post('/updatepassword');

        $this->assertResponseIsSuccessful($response);
        $this->assertResponseBodyContains('<h2 class="headline">Reset My Password</h2>', $response);
    }
}
