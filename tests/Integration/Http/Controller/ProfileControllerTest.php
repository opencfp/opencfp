<?php

namespace OpenCFP\Test\Integration\Http\Controller;

use OpenCFP\Domain\Model\User;
use OpenCFP\Test\Helper\RefreshDatabase;
use OpenCFP\Test\WebTestCase;

/**
 * Class ProfileControllerTest
 *
 * @group db
 */
class ProfileControllerTest extends WebTestCase
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
        $this->asLoggedInSpeaker(1)
            ->get('/profile/edit/2')
            ->assertNotSee('My Profile')
            ->assertRedirect();
    }

    /**
     * @test
     */
    public function seeEditPageWhenAllowed()
    {
        $id = self::$user->id;

        $this->asLoggedInSpeaker($id)
            ->get('/profile/edit/'. $id)
            ->assertSuccessful();
    }

    /**
     * @test
     */
    public function notAbleToEditOtherPersonsProfile()
    {
        $this->asLoggedInSpeaker(1)
            ->post('/profile/edit', ['id' =>2])
            ->assertNotSee('My Profile')
            ->assertRedirect();
    }

    /**
     * @test
     */
    public function canNotUpdateProfileWithInvalidData()
    {
        $this->asLoggedInSpeaker()
            ->post('/profile/edit', $this->putUserInRequest(false))
            ->assertSee('My Profile')
            ->assertSee('Invalid email address format')
            ->assertSuccessful();
    }

    /**
     * @test
     */
    public function redirectToDashboardOnSuccessfulUpdate()
    {
        $user = self::$user;
        $this->asLoggedInSpeaker($user->id)
            ->post('/profile/edit', $this->putUserInRequest(true, $user->id))
            ->assertNotSee('My Profile')
            ->assertRedirect();
    }

    /**
     * @test
     */
    public function displayChangePasswordWhenAllowed()
    {
        $this->asLoggedInSpeaker()
            ->get('/profile/change_password')
            ->assertSee('Change Your Password')
            ->assertSuccessful();
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
