<?php

namespace OpenCFP\Test\Http\Controller\Admin;

use Mockery as m;
use OpenCFP\Application;
use OpenCFP\Environment;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockFileSessionStorage;

class SpeakersControllerTest extends \PHPUnit\Framework\TestCase
{
    private $app;

    protected function setUp()
    {
        // Create our Application object
        $this->app = new Application(BASE_PATH, Environment::testing());
        $this->app['session.test'] = true;

        // Create a test double for our User entity
        $user = m::mock(\OpenCFP\Domain\Entity\User::class);
        $user->shouldReceive('hasPermission')->with('admin')->andReturn(true);
        $user->shouldReceive('getId')->andReturn(1);
        $user->shouldReceive('hasAccess')->with('admin')->andReturn(true);

        // Create a test double for our Sentry object
        $sentry = m::mock('Cartalyst\Sentry\Sentry');
        $sentry->shouldReceive('check')->andReturn(true);
        $sentry->shouldReceive('getUser')->andReturn($user);
        $this->app['sentry'] = $sentry;
        $this->app['user'] = $user;
    }

    /**
     * Verify that not found speaker redirects and sets flash error message
     *
     * @test
     */
    public function speakerNotFoundHasFlashMessage()
    {
        $speakerId = uniqid();

        // Override our mapper with the double
        $spot = m::mock('Spot\Locator');
        $mapper = m::mock(\OpenCFP\Domain\Entity\Mapper\User::class);
        $mapper->shouldReceive('get')
            ->andReturn([]);

        $spot->shouldReceive('mapper')
            ->with(\OpenCFP\Domain\Entity\User::class)
            ->andReturn($mapper);
        $this->app['spot'] = $spot;

        // Use our pre-configured Application object
        ob_start();
        $this->app->run();
        ob_end_clean();

        // Create our Request object
        $req = m::mock('Symfony\Component\HttpFoundation\Request');
        $req->shouldReceive('get')->with('id')->andReturn($speakerId);

        // Execute the controller and capture the output
        $controller = new \OpenCFP\Http\Controller\Admin\SpeakersController();
        $controller->setApplication($this->app);
        $response = $controller->viewAction($req);

        $this->assertInstanceOf(
            'Symfony\Component\HttpFoundation\RedirectResponse',
            $response
        );

        $this->assertContains(
            'Could not find requested speaker',
            $this->app['session']->get('flash')
        );
    }

    /**
     * @test
     */
    public function speaker_talks_comments_and_meta_should_be_removed_when_speaker_is_deleted()
    {
        $spot = m::mock(\Spot\Locator::class);
        $speaker = m::mock(\OpenCFP\Domain\Entity\User::class);
        $talk = m::mock(\OpenCFP\Domain\Entity\Talk::class);
        $userMapper = m::mock(\Spot\Mapper::class);
        $talkMapper = m::mock(\Spot\Mapper::class);
        $talkCommentsMapper = m::mock(\Spot\Mapper::class);
        $talkMetaMapper = m::mock(\Spot\Mapper::class);

        // Create a session object
        $this->app['session'] = new Session(new MockFileSessionStorage);

        // Use our pre-configured Application object
        ob_start();
        $this->app->run();
        ob_end_clean();

        // We're deleting speaker numero uno
        $request = m::mock(Request::class);
        $request->shouldReceive('get')->with('id')->atLeast()->once()->andReturn(1);

        // We get the speaker from mapper
        $userMapper->shouldReceive('get')->with(1)->once()->andReturn($speaker);

        // Speaker is handed off to `removeSpeakerTalks`
        // and we get all their talks in a collection
        // Note: this is the `talks` relation, but under the hood
        //       Spot actually calls the relation method on entity
        $speaker->shouldReceive('relation->execute')->once()->andReturn([$talk]);

        // That talk also has comments and meta information
        // Note: these actually return entities for comment and meta
        //       but in the interest of brevity are faked as strings
        $talk->shouldReceive('relation->execute')->times(2)->andReturn(['comment'], ['meta']);

        // We loop over each of those and delete them using mapper
        $talkCommentsMapper->shouldReceive('delete')->once()->with('comment');
        $talkMetaMapper->shouldReceive('delete')->once()->with('meta');

        // Finally, we delete the talk itself
        $talkMapper->shouldReceive('delete')->once()->with($talk);

        // Once all those pesky talks are gone, we remove the speaker!
        $userMapper->shouldReceive('delete')->once()->with($speaker);

        // So lets wire all this into spot locator and replace
        // into the application instance
        $spot->shouldReceive('mapper')->with(\OpenCFP\Domain\Entity\User::class)->andReturn($userMapper);
        $spot->shouldReceive('mapper')->with(\OpenCFP\Domain\Entity\Talk::class)->andReturn($talkMapper);
        $spot->shouldReceive('mapper')->with(\OpenCFP\Domain\Entity\TalkComment::class)->andReturn($talkCommentsMapper);
        $spot->shouldReceive('mapper')->with(\OpenCFP\Domain\Entity\TalkMeta::class)->andReturn($talkMetaMapper);

        // All of this stuff should be done in a transaction
        $spot->shouldReceive('config->connection->beginTransaction')->once();
        $spot->shouldReceive('config->connection->commit')->once();

        unset($this->app['spot']);
        $this->app['spot'] = $spot;

        // Execute the controller and capture the output
        $controller = new \OpenCFP\Http\Controller\Admin\SpeakersController();
        $controller->setApplication($this->app);
        $response = $controller->deleteAction($request);

        // Assert mock expectations.
        m::close();

        $this->assertInstanceOf(RedirectResponse::class, $response);
    }
}
