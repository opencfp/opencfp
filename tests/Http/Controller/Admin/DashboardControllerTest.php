<?php

namespace OpenCFP\Test\Http\Controller\Admin;

use Cartalyst\Sentry\Users\UserInterface;
use Mockery as m;
use OpenCFP\Domain\Services\Authentication;
use OpenCFP\Test\TestCase;

/**
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 *
 * These slow down the tests a bit, but it is required for our overrides to work.
 */
class DashboardControllerTest extends TestCase
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

        // Create a test double for Sentry
        $auth = m::mock(Authentication::class);
        $auth->shouldReceive('check')->andReturn(true);
        $auth->shouldReceive('user')->andReturn($user);
        $this->swap(Authentication::class, $auth);

        /**
         * Mocking for the user and Favorite models is done with overload: and full namespaces.
         * This is to force mockery to override the ones generated with the new keyword as well.
         * We can't use them in the top of the file like normal classes either as that messes with the magic.
         */
        $userMock = m::mock('overload:' . \OpenCFP\Domain\Model\User::class);
        $userMock->shouldReceive('all->count')->andReturn('1');
        $this->swap(\OpenCFP\Domain\Model\User::class, $userMock);

        $favoriteMock = m::mock('overload:' . \OpenCFP\Domain\Model\Favorite::class);
        $favoriteMock->shouldReceive('all->count')->andReturn('1');
        $this->swap(\OpenCFP\Domain\Model\User::class, $favoriteMock);
        $this->swap(\OpenCFP\Domain\Model\Favorite::class, $favoriteMock);

        $this->talkListMock();

        $this->get('/admin')
            ->assertSuccessful()
            ->assertSee('First Talk')
            ->assertSee('The bug slayer strikes again!')
            ->assertSee('The Bug Slayer')
            ->assertSee('<h2>10</h2>');
    }

    /**
     * Helper function that mocks a few talks for us.
     */
    private function talkListMock()
    {
        $talk = m::mock('overload:' . \OpenCFP\Domain\Model\Talk::class);
        $talk->shouldReceive('all')->andReturn($talk);
        $talk->shouldReceive('count')->andReturn(10);
        $talk->shouldReceive('where')->andReturn($talk);
        $talk->shouldReceive('getRecent')->andReturn([
            [
                'id' => 1,
                'title' => 'First Talk',
                'type' => 'regular',
                'category' => 'api',
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
        $this->swap(\OpenCFP\Domain\Model\Talk::class, $talk);
    }
}
