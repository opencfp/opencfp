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
use OpenCFP\Domain\Services\Authentication;
use OpenCFP\Domain\Speaker\SpeakerProfile;
use OpenCFP\Infrastructure\Auth\UserInterface;
use OpenCFP\Test\Integration\WebTestCase;

/**
 * @group db
 * @coversNothing
 */
final class DashboardControllerTest extends WebTestCase
{
    /**
     * Test that the index page returns a list of talks associated
     * with a specific user and information about that user as well
     *
     * @test
     */
    public function indexDisplaysUserAndTalks()
    {
        $user = m::mock(UserInterface::class);
        $user->shouldReceive('id')->andReturn(1);
        $user->shouldReceive('getId')->andReturn(1);
        $user->shouldReceive('hasAccess')->with('admin')->andReturn(true);
        $user->shouldReceive('hasAccess')->with('reviewer')->andReturn(false);

        $auth = m::mock(Authentication::class);
        $auth->shouldReceive('isAuthenticated')->andReturn(true);
        $auth->shouldReceive('user')->andReturn($user);
        $this->swap(Authentication::class, $auth);

        // Create a test double for a talk in profile
        $talk = m::mock(\stdClass::class);
        $talk->shouldReceive('title')->andReturn('Test Title');
        $talk->shouldReceive('description')->andReturn('Awesome talk');
        $talk->shouldReceive('id')->andReturn(1);
        $talk->shouldReceive('type', 'category', 'created_at');

        // Create a test double for profile
        $profile = m::mock(\OpenCFP\Domain\Speaker\SpeakerProfile::class);
        $profile->shouldReceive('getName')->andReturn('Test User');
        $profile->shouldReceive('getPhoto', 'getCompany', 'getTwitter', 'getUrl', 'getAirport', 'getBio', 'getInfo', 'getTransportation', 'getHotel');
        $profile->shouldReceive('getTalks')->andReturn([$talk]);
        $profile->shouldReceive('needsProfile')->andReturn(false);

        $speakerService = m::mock(\OpenCFP\Application\Speakers::class);
        $speakerService->shouldReceive('findProfile')->andReturn($profile);
        $this->swap('application.speakers', $speakerService);

        $this->callForPapersIsOpen();

        $response = $this->get('/dashboard');

        $this->assertResponseIsSuccessful($response);
        $this->assertResponseBodyContains('Test Title', $response);
        $this->assertResponseBodyContains('Test User', $response);
    }

    /**
     * @test
     */
    public function it_hides_transportation_and_hotel_when_doing_an_online_conference()
    {
        $faker = $this->faker();

        $user = m::mock(UserInterface::class);
        $user->shouldReceive('hasPermission')->with('admin')->andReturn(true);
        $user->shouldReceive('getId')->andReturn(1);
        $user->shouldReceive('id')->andReturn(1);
        $user->shouldReceive('hasAccess')->with('admin')->andReturn(false);
        $auth = m::mock(Authentication::class);
        $auth->shouldReceive('isAuthenticated')->andReturn(true);
        $auth->shouldReceive('user')->andReturn($user);
        $this->swap(Authentication::class, $auth);
        $this->swap('user', $user);

        // Create a test double for SpeakerProfile
        // We  have benefit of being able to stub an application
        // service for this.
        $profile = $this->stubProfileWith([
            'getTalks'          => [],
            'getName'           => $faker->name,
            'getEmail'          => $faker->companyEmail,
            'getCompany'        => $faker->company,
            'getTwitter'        => $faker->userName,
            'getUrl'            => $faker->url,
            'getInfo'           => $faker->text,
            'getBio'            => $faker->text,
            'getTransportation' => true,
            'getHotel'          => true,
            'getAirport'        => 'RDU',
            'getPhoto'          => '',
        ]);

        $speakersDouble = m::mock(\OpenCFP\Application\Speakers::class)
            ->shouldReceive('findProfile')
            ->andReturn($profile)
            ->getMock();

        $this->swap('application.speakers', $speakersDouble);

        $response = $this
            ->callForPapersIsOpen()
            ->isOnlineConference()
            ->get('/dashboard');

        $this->assertResponseBodyNotContains('Need Transportation', $response);
        $this->assertResponseBodyNotContains('Need Hotel', $response);
    }

    private function stubProfileWith(array $stubMethods = []): SpeakerProfile
    {
        $speakerProfileDouble = m::mock(SpeakerProfile::class);
        $speakerProfileDouble->shouldReceive($stubMethods);

        return $speakerProfileDouble;
    }
}
