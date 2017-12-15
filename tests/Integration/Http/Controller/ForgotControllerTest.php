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

namespace OpenCFP\Test\Integration\Http\Controller;

use Mockery as m;
use OpenCFP\Domain\Services\AccountManagement;
use OpenCFP\Test\Helper\DataBaseInteraction;
use OpenCFP\Test\Integration\WebTestCase;

final class ForgotControllerTest extends WebTestCase
{
    use DataBaseInteraction;

    /**
     * Test that index action displays a form that allows the user to reset
     * their password
     *
     * @test
     */
    public function indexDisplaysCorrectForm()
    {
        $response = $this->get('/forgot');

        // Get the form object and verify things look correct
        $this->assertContains(
            '<form id="forgot"',
            $response->getContent()
        );
        $this->assertContains(
            '<input type="hidden" id="forgot_form__token"',
            $response->getContent()
        );
        $this->assertContains(
            '<input type="email" id="forgot_form_email"',
            $response->getContent()
        );
    }

    /**
     * @test
     */
    public function sendResetDisplaysCorrectMessage()
    {
        $this->container->get(AccountManagement::class)
            ->create('someone@example.com', 'some password');

        // Override our reset_emailer service
        $resetEmailer = m::mock(\OpenCFP\Domain\Services\ResetEmailer::class);
        $resetEmailer->shouldReceive('send')->andReturn(true);
        $this->swap('reset_emailer', $resetEmailer);

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

    /**
     * @test
     */
    public function resetPasswordHandlesNotSendingResetEmailCorrectly()
    {
        $this->container->get(AccountManagement::class)
            ->create('someone@example.com', 'some password');

        // Override our reset_emailer service
        $resetEmailer = m::mock(\OpenCFP\Domain\Services\ResetEmailer::class);
        $resetEmailer->shouldReceive('send')->andReturn(false);
        $this->swap('reset_emailer', $resetEmailer);

        $client = $this->createClient();

        $form = $client->request('GET', '/forgot')
            ->selectButton('Reset Password')
            ->form();

        $form->setValues(['forgot_form' => ['email' => 'someone@example.com']]);

        $client->followRedirects();
        $client->submit($form);

        // As long as the email validates as being a potential email, the flash message should indicate success
        $this->assertContains(
            'We were unable to send your password reset request. Please try again',
            $client->getResponse()->getContent()
        );
    }
}
