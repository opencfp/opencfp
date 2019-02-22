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

final class IndexActionTest extends WebTestCase
{
    /**
     * @test
     */
    public function indexDisplaysCorrectForm()
    {
        $response = $this->get('/forgot');

        $this->assertContains('<form id="forgot"', $response->getContent());
        $this->assertContains('<input type="hidden" id="forgot_form__token"', $response->getContent());
        $this->assertContains('<input type="email" id="forgot_form_email"', $response->getContent());
    }
}
