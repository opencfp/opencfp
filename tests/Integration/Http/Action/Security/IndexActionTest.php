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

namespace OpenCFP\Test\Integration\Http\Action\Security;

use OpenCFP\Test\Integration\WebTestCase;

final class IndexActionTest extends WebTestCase
{
    /**
     * @test
     */
    public function indexShowsLoginForm()
    {
        $this->callForPapersIsOpen();

        $response = $this->get('/login');

        $this->assertResponseIsSuccessful($response);
        $this->assertResponseBodyContains('Email', $response);
        $this->assertResponseBodyContains('Password', $response);
        $this->assertResponseBodyContains('Login', $response);
    }
}
