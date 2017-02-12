<?php

namespace OpenCFP\Test\Http\Controller;

use Cartalyst\Sentry\Sentry;
use Mockery as m;
use OpenCFP\Application;
use OpenCFP\Domain\Speaker\SpeakerProfile;
use OpenCFP\Environment;
use OpenCFP\Http\Controller\DashboardController;
use OpenCFP\Test\Util\Faker\GeneratorTrait;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockFileSessionStorage;
use Twig_Environment;

class DashboardControllerTest extends \PHPUnit_Framework_TestCase
{
    use GeneratorTrait;

    /**
     * Test that the index page returns a list of talks associated
     * with a specific user and information about that user as well
     *
     * @test
     */
    public function indexDisplaysUserAndTalks()
    {
        $app = new Application(BASE_PATH, Environment::testing());
        $app['session'] = new Session(new MockFileSessionStorage());

        // Set things up so Sentry believes we're logged in
        $user = m::mock('StdClass');
        $user->shouldReceive('id')->andReturn(1);
        $user->shouldReceive('getId')->andReturn(1);
        $user->shouldReceive('hasAccess')->with('admin')->andReturn(true);

        // Create a test double for Sentry
        $sentry = m::mock(Sentry::class);
        $sentry->shouldReceive('check')->times(3)->andReturn(true);
        $sentry->shouldReceive('getUser')->andReturn($user);
        $app['sentry'] = $sentry;

        // Create a test double for a talk in profile
        $talk = m::mock('StdClass');
        $talk->shouldReceive('title')->andReturn('Test Title');
        $talk->shouldReceive('id')->andReturn(1);
        $talk->shouldReceive('type', 'category', 'created_at');

        // Create a test double for profile
        $profile = m::mock('StdClass');
        $profile->shouldReceive('name')->andReturn('Test User');
        $profile->shouldReceive('photo', 'company', 'twitter', 'airport', 'bio', 'info', 'transportation', 'hotel');
        $profile->shouldReceive('talks')->andReturn([$talk]);

        $speakerService = m::mock('StdClass');
        $speakerService->shouldReceive('findProfile')->andReturn($profile);

        $app['application.speakers'] = $speakerService;

        ob_start();
        $app->run();  // Fire before handlers... boot...
        ob_end_clean();

        // Instantiate the controller and run the indexAction
        $controller = new \OpenCFP\Http\Controller\DashboardController();
        $controller->setApplication($app);

        $response = $controller->showSpeakerProfile();
        $this->assertContains('Test Title', (string) $response);
        $this->assertContains('Test User', (string) $response);
    }

    /**
     * @test
     */
    public function it_hides_transportation_and_hotel_when_doing_an_online_conference()
    {
        $faker = $this->getFaker();
        $app = new Application(BASE_PATH, Environment::testing());
        $app['session'] = new Session(new MockFileSessionStorage());

        // Specify configuration to enable `online_conference` settings.
        // TODO Bake something like this as a trait. Dealing with mocking
        // TODO services like configuration and template rending is painful.
        $config = $app['config']['application'];
        $config['online_conference'] = true;

        /* @var Twig_Environment $twig */
        $twig = $app['twig'];

        $twig->addGlobal('site', $config);

        // There's some global before filters that call Sentry directly.
        // We have to stub that behaviour here to have it think we are not admin.
        // TODO This stuff is everywhere. Bake it into a trait for testing in the short-term.
        $user = m::mock('stdClass');
        $user->shouldReceive('hasPermission')->with('admin')->andReturn(true);
        $user->shouldReceive('getId')->andReturn(1);
        $user->shouldReceive('id')->andReturn(1);
        $user->shouldReceive('hasAccess')->with('admin')->andReturn(false);
        $sentry = m::mock(Sentry::class);
        $sentry->shouldReceive('check')->andReturn(true);
        $sentry->shouldReceive('getUser')->andReturn($user);
        $app['sentry'] = $sentry;
        $app['user'] = $user;

        // Create a test double for SpeakerProfile
        // We  have benefit of being able to stub an application
        // service for this.
        $profile = $this->stubProfileWith([
            'getTalks' => [],
            'getName' => $faker->name,
            'getEmail' => $faker->companyEmail,
            'getCompany' => $faker->company,
            'getTwitter' => $faker->userName,
            'getInfo' => $faker->text,
            'getBio' => $faker->text,
            'getTransportation' => true,
            'getHotel' => true,
            'getAirport' => 'RDU',
            'getPhoto' => '',
        ]);

        $speakersDouble = m::mock(\OpenCFP\Application\Speakers::class)
            ->shouldReceive('findProfile')
            ->andReturn($profile)
            ->getMock();

        $app['application.speakers'] = $speakersDouble;

        ob_start();
        $app->run();  // Fire before handlers... boot...
        ob_end_clean();

        // Instantiate the controller and run the indexAction
        $controller = new \OpenCFP\Http\Controller\DashboardController();
        $controller->setApplication($app);

        $response = (string) $controller->showSpeakerProfile();

        $this->assertNotContains('Need Transportation', $response);
        $this->assertNotContains('Need Hotel', $response);
    }

    private function stubProfileWith($stubMethods = [])
    {
        $speakerProfileDouble = m::mock(SpeakerProfile::class);
        $speakerProfileDouble->shouldReceive($stubMethods);
        return $speakerProfileDouble;
    }

    /**
     * @test
     * @dataProvider checkWhetherCfpIsOpenOrNotWorksAsExpectedProvider
     */
    public function checkWhetherCfpIsOpenOrNotWorksAsExpected($currentTime, $cfpTime, $expected)
    {
        $app = m::mock(Application::class);
        $app->shouldReceive('config')->with('application.enddate')->andReturn($cfpTime);
        $controller = new DashboardController();
        $controller->setApplication($app);

        $this->assertSame($expected, $controller->isCfpOpen($currentTime));
    }

    public function checkWhetherCfpIsOpenOrNotWorksAsExpectedProvider()
    {
        $tomorrow = (new \DateTime('+ 1 day'))->format('Y-m-d');
        $yesterday = (new \DateTime('- 1 day'))->format('Y-m-d');
        return [
            [strtotime('2017-12-09T12:34:45'), '2017-12-10', true],
            [strtotime('2017-12-11T12:34:45'), '2017-12-10', false],
            [0, $tomorrow, true],
            [0, $yesterday, false],
            [new \DateTime('2017-12-09'), '2017-12-10', true],
            [new \DateTime('2017-12-11'), '2017-12-10', false],
            [new \DateTime('2017-12-10T23:58:59'), '2017-12-10', true],
            [new \DateTime('2017-12-10T23:59:01'), '2017-12-10', false],
            [new \DateTime('2017-12-10T16:59:59'), '2017-12-10T17:00:00', true],
            [new \DateTime('2017-12-10T17:00:01'), '2017-12-10T17:00:00', false],
        ];
    }
}
