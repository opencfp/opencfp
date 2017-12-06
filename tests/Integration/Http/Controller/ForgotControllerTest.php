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
use OpenCFP\Infrastructure\Auth\UserInterface;
use OpenCFP\Test\Integration\WebTestCase;

/**
 * @group db
 * @covers \OpenCFP\Http\Controller\ForgotController
 */
final class ForgotControllerTest extends WebTestCase
{
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
        $accounts = m::mock(AccountManagement::class);
        $accounts->shouldReceive('findByLogin')->andReturn($this->createUser());
        $this->swap(AccountManagement::class, $accounts);

        // Override our reset_emailer service
        $resetEmailer = m::mock(\OpenCFP\Domain\Services\ResetEmailer::class);
        $resetEmailer->shouldReceive('send')->andReturn(true);
        $this->swap('reset_emailer', $resetEmailer);

        // We need to create a replacement form.factory to return a form we control
        $formFactory = m::mock(\Symfony\Component\Form\FormFactoryInterface::class);
        $formFactory->shouldReceive('createBuilder->getForm')->andReturn($this->createForm('valid'));
        $this->swap('form.factory', $formFactory);

        $this->post('/forgot');

        // As long as the email validates as being a potential email, the flash message should indicate success
        $flashMessage = $this->container->get('session')->get('flash');
        $this->assertContains(
            'If your email was valid, we sent a link to reset your password to',
            $flashMessage['ext']
        );
    }

    /**
     * @test
     */
    public function invalidResetFormTriggersErrorMessage()
    {
        $formFactory = m::mock(\Symfony\Component\Form\FormFactoryInterface::class);
        $formFactory->shouldReceive('createBuilder->getForm')->andReturn($this->createForm('not valid'));
        $this->swap('form.factory', $formFactory);

        $this->post('/forgot');

        $flashMessage = $this->container->get('session')->get('flash');
        $this->assertContains(
            'Please enter a properly formatted email address',
            $flashMessage['ext']
        );
    }

    /**
     * @test
     */
    public function resetPasswordNotFindingUserCorrectlyDisplaysMessage()
    {
        $formFactory = m::mock(\Symfony\Component\Form\FormFactoryInterface::class);
        $formFactory->shouldReceive('createBuilder->getForm')->andReturn($this->createForm('valid'));
        $this->swap('form.factory', $formFactory);

        $this->post('/forgot');

        $flashMessage = $this->container->get('session')->get('flash');
        $this->assertContains(
            'If your email was valid, we sent a link to reset your password to',
            $flashMessage['ext']
        );
    }

    /**
     * @test
     */
    public function resetPasswordHandlesNotSendingResetEmailCorrectly()
    {
        $accounts = m::mock(AccountManagement::class);
        $accounts->shouldReceive('findByLogin')->andReturn($this->createUser());
        $this->swap(AccountManagement::class, $accounts);

        // Override our reset_emailer service
        $resetEmailer = m::mock(\OpenCFP\Domain\Services\ResetEmailer::class);
        $resetEmailer->shouldReceive('send')->andReturn(false);
        $this->swap('reset_emailer', $resetEmailer);

        // We need to create a replacement form.factory to return a form we control
        $formFactory = m::mock(\Symfony\Component\Form\FormFactoryInterface::class);
        $formFactory->shouldReceive('createBuilder->getForm')->andReturn($this->createForm('valid'));
        $this->swap('form.factory', $formFactory);

        $this->post('/forgot');

        // As long as the email validates as being a potential email, the flash message should indicate success
        $flashMessage = $this->container->get('session')->get('flash');
        $this->assertContains(
            'We were unable to send your password reset request. Please try again',
            $flashMessage['ext']
        );
    }

    private function createUser(): UserInterface
    {
        $user = m::mock(UserInterface::class);
        $user->shouldReceive('getResetPasswordCode');
        $user->shouldReceive('getId');

        return $user;
    }

    private function createForm($validStatus): \OpenCFP\Http\Form\ForgotForm
    {
        $isValid = ($validStatus == 'valid');
        $form    = m::mock(\OpenCFP\Http\Form\ForgotForm::class);
        $form->shouldReceive('handleRequest');
        $form->shouldReceive('isValid')->andReturn($isValid);
        $data = ['email' => 'test@opencfp.org'];
        $form->shouldReceive('getData')->andReturn($data);

        return $form;
    }
}
