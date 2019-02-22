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

namespace OpenCFP\Test\Integration\Http\Action\Signup;

use OpenCFP\Test\Integration\WebTestCase;

final class PrivacyActionTest extends WebTestCase
{
    /**
     * @test
     */
    public function privacyPolicyRenders()
    {
        $response = $this->get('/privacy');
        $this->assertResponseIsSuccessful($response);
        $this->assertResponseBodyContains('Privacy Policy', $response);
        $this->assertResponseBodyContains('General Data Protection Regulation', $response);
    }
}
