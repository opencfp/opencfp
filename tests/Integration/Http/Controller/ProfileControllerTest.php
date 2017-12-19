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

use OpenCFP\Domain\Model\User;
use OpenCFP\Test\Helper\RefreshDatabase;
use OpenCFP\Test\Integration\WebTestCase;

final class ProfileControllerTest extends WebTestCase
{
    use RefreshDatabase;

    private static $user;

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
        self::$user = factory(User::class, 1)->create()->first();
    }

    /**
     * @test
     */
    public function notAbleToSeeEditPageOfOtherPersonsProfile()
    {
        $response = $this
            ->asLoggedInSpeaker(1)
            ->get('/profile/edit/2');

        $this->assertResponseBodyNotContains('My Profile', $response);
        $this->assertResponseIsRedirect($response);
    }

    /**
     * @test
     */
    public function seeEditPageWhenAllowed()
    {
        $id = self::$user->id;

        $response = $this
            ->asLoggedInSpeaker($id)
            ->get('/profile/edit/' . $id);

        $this->assertResponseIsSuccessful($response);
    }

    /**
     * @test
     */
    public function notAbleToEditOtherPersonsProfile()
    {
        $response = $this
            ->asLoggedInSpeaker(1)
            ->post('/profile/edit', [
                'id' => 2,
            ]);

        $this->assertResponseBodyNotContains('My Profile', $response);
        $this->assertResponseIsRedirect($response);
    }

    /**
     * @test
     */
    public function canNotUpdateProfileWithInvalidData()
    {
        $response = $this
            ->asLoggedInSpeaker()
            ->post('/profile/edit', $this->putUserInRequest(false));

        $this->assertResponseIsSuccessful($response);
        $this->assertResponseBodyContains('My Profile', $response);
        $this->assertResponseBodyContains('Invalid email address format', $response);
    }

    /**
     * @test
     */
    public function redirectToDashboardOnSuccessfulUpdate()
    {
        $user = self::$user;

        $response = $this
            ->asLoggedInSpeaker($user->id)
            ->post('/profile/edit', $this->putUserInRequest(true, $user->id));

        $this->assertResponseBodyNotContains('My Profile', $response);
        $this->assertResponseIsRedirect($response);
    }

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

    /**
     * Helper function to fake a user in the request object.
     *
     * @param $isEmailValid bool whether or not to use a valid email address
     *
     * @return array
     */
    private function putUserInRequest($isEmailValid, $id = 1): array
    {
        return [
            'id'         => $id,
            'email'      => $isEmailValid ? 'valideamial@cfp.org' : 'invalidEmail',
            'first_name' => 'First',
            'last_name'  => 'Last',
        ];
    }
}
