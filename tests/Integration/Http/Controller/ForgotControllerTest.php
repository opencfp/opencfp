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

namespace OpenCFP\Test\Integration\Http\Controller;

use OpenCFP\Domain\Services\AccountManagement;
use OpenCFP\Test\Integration\TransactionalTestCase;
use OpenCFP\Test\Integration\WebTestCase;

final class ForgotControllerTest extends WebTestCase implements TransactionalTestCase
{
    /**
     * @test
     */
    public function sendResetDisplaysCorrectMessage()
    {
        $this->container->get(AccountManagement::class)
            ->create('someone@example.com', 'some password');

        $client = $this->createClient();

        $form = $client->request('GET', '/forgot')
            ->selectButton('Reset Password')
            ->form();

        $form->setValues(['forgot_form' => ['email' => 'someone@example.com']]);

        $client->followRedirects();
        $client->submit($form);

        $this->assertContains(
            'If your email was valid, we sent a link to reset your password to',
            $client->getResponse()->getContent()
        );
    }

    /**
     * @test
     */
    public function invalidResetFormTriggersErrorMessage()
    {
        $client = $this->createClient();

        $form = $client->request('GET', '/forgot')
            ->selectButton('Reset Password')
            ->form();

        $form->setValues(['forgot_form' => ['email' => 'INVALID']]);

        $client->followRedirects();
        $client->submit($form);

        $this->assertContains(
            'Please enter a properly formatted email address',
            $client->getResponse()->getContent()
        );
    }

    /**
     * @test
     */
    public function resetPasswordNotFindingUserCorrectlyDisplaysMessage()
    {
        $client = $this->createClient();

        $form = $client->request('GET', '/forgot')
            ->selectButton('Reset Password')
            ->form();

        $form->setValues(['forgot_form' => ['email' => 'someone@example.com']]);

        $client->followRedirects();
        $client->submit($form);

        $this->assertContains(
            'If your email was valid, we sent a link to reset your password to',
            $client->getResponse()->getContent()
        );
    }
}
