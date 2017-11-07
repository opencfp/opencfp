<?php

namespace OpenCFP\Test\Http\Controller\Admin;

use Cartalyst\Sentry\Users\UserInterface;
use Mockery as m;
use OpenCFP\Domain\Services\Authentication;
use OpenCFP\Test\WebTestCase;

/**
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 *
 * These slow down the tests a bit, but it is required for our overrides to work.
 */
class DashboardControllerTest extends WebTestCase
{

    /**
     * @test
     */
    public function indexDisplaysListOfTalks()
    {
        // Set things up so Sentry believes we're logged in
        $user = m::mock(UserInterface::class);
        $user->shouldReceive('id')->andReturn(1);
        $user->shouldReceive('getId')->andReturn(1);
        $user->shouldReceive('hasAccess')->with('admin')->andReturn(true);
        $user->shouldReceive('hasPermission')->with('admin')->andReturn(true);
        $user->shouldReceive('hasAccess')->with('reviewer')->andReturn(false);
        $user->shouldReceive('hasPermission')->with('reviewer')->andReturn(false);

        // Create a test double for Sentry
        $auth = m::mock(Authentication::class);
        $auth->shouldReceive('check')->andReturn(true);
        $auth->shouldReceive('user')->andReturn($user);
        $auth->shouldReceive('userId')->andReturn(1);
        $this->swap(Authentication::class, $auth);

        /**
         * Mocking for the user and Favorite models is done with overload: and full namespaces.
         * This is to force mockery to override the ones generated with the new keyword as well.
         * We can't make use of the 'use' keyword to import them since that messes with the magic.
         */
        $userMock = m::mock('overload:' . \OpenCFP\Domain\Model\User::class);
        $userMock->shouldReceive('count')->andReturn('1');
        $this->swap(\OpenCFP\Domain\Model\User::class, $userMock);

        $favoriteMock = m::mock('overload:' . \OpenCFP\Domain\Model\Favorite::class);
        $favoriteMock->shouldReceive('count')->andReturn('1');
        $this->swap(\OpenCFP\Domain\Model\Favorite::class, $favoriteMock);

        $this->talkListMock();

        $this->get('/admin/')
            ->assertSuccessful()
            ->assertSee('First Talk')
            ->assertSee('The bug slayer strikes again!')
            ->assertSee('The Bug Slayer')
            ->assertSee('10</div>');
    }

    /**
     * Helper function that mocks a few talks for us.
     */
    private function talkListMock()
    {
        $talk = m::mock('overload:' . \OpenCFP\Domain\Model\Talk::class);
        $talk->shouldReceive('count')->andReturn(10);
        $talk->shouldReceive('where')->andReturn($talk);
        $talk->shouldReceive('recent->get');

        $formatted = m::mock('overload:' . \OpenCFP\Domain\Talk\TalkFormatter::class);
        $formatted->shouldReceive('formatList')->andReturn([
            [
                'id' => 1,
                'title' => 'First Talk',
                'type' => 'regular',
                'category' => 'api',
                'description' => 'Talk is amazing',
                'created_at' => new \DateTime(),
                'user' => [
                    'id' => 1,
                    'first_name' => 'Speaker',
                    'last_name' => 'Name',
                ],
                'meta' => [
                    'rating' => 0,
                ],
                'favorite' => 0,
                'selected' => 0,
            ],
            [
                'id' => 2,
                'title' => 'The bug slayer strikes again!',
                'type' => 'regular',
                'category' => 'api',
                'description' => 'Talk is amazing',
                'created_at' => new \DateTime(),
                'user' => [
                    'id' => 1,
                    'first_name' => 'Daan',
                    'last_name' => 'The Bug Slayer',
                ],
                'meta' => [
                    'rating' => 0,
                ],
                'favorite' => 0,
                'selected' => 1,
            ],
        ]);
        $this->swap(\OpenCFP\Domain\Talk\TalkFormatter::class, $formatted);

        $this->swap(\OpenCFP\Domain\Model\Talk::class, $talk);
    }
}
