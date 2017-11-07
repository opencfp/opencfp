<?php

namespace OpenCFP\Test\Http\Controller;

use Cartalyst\Sentry\Users\UserInterface;
use Mockery as m;
use OpenCFP\Application;
use OpenCFP\Domain\Services\Authentication;
use OpenCFP\Environment;
use OpenCFP\Http\Controller\ProfileController;
use Spot\Locator;

/**
 * Class ProfileControllerTest
 * @package OpenCFP\Test\Http\Controller
 * @group db
 */
class ProfileControllerTest extends \PHPUnit\Framework\TestCase
{
    private $app;
    private $req;

    public function setUp()
    {
        // Create our Application object
        $this->app = new Application(BASE_PATH, Environment::testing());
        $this->app['session.test'] = true;

        $user = m::mock(UserInterface::class);
        $user->shouldReceive('hasPermission')->with('admin')->andReturn(false);
        $user->shouldReceive('getId')->andReturn(1);
        $user->shouldReceive('id')->andReturn(1);
        $user->id = 1;
        $user->shouldReceive('hasAccess')->with('admin')->andReturn(false);
        $user->shouldReceive('getLogin')->andReturn('my@email.com');

        $auth = m::mock(Authentication::class);
        $auth->shouldReceive('check')->andReturn(true);
        $auth->shouldReceive('user')->andReturn($user);
        $this->app[Authentication::class] = $auth;

        // Use our pre-configured Application object
        ob_start();
        $this->app->run();
        ob_end_clean();

        $this->req = m::mock('Symfony\Component\HttpFoundation\Request');
    }

    /**
     * @test
     */
    public function notAbleToSeeEditPageOfOtherPersonsProfile()
    {
        $this->req->shouldReceive('get')->with('id')->andReturn('2');

        $controller = new ProfileController();
        $controller->setApplication($this->app);

        $response = $controller->editAction($this->req);

        $flash= $this->app['session']->get('flash');

        $this->assertInstanceOf(
            'Symfony\Component\HttpFoundation\RedirectResponse',
            $response
        );
        $this->assertEquals('error', $flash['type']);
        $this->assertEquals("You cannot edit someone else's profile", $flash['ext']);
        $this->assertContains('dashboard', $response->getTargetUrl());
    }

    /**
     * @test
     */
    public function seeEditPageWhenAllowed()
    {
        $controller = new ProfileController();
        $controller->setApplication($this->app);

        $this->req->shouldReceive('get')->with('id')->andReturn('1');

        $spot = m::mock(Locator::class);
        $spot->shouldReceive('mapper')->with('\OpenCFP\Domain\Entity\User')->andReturn($spot);
        $spot->shouldReceive('get')->with($this->app[Authentication::class]->user()->getId())->andReturn($spot);
        $spot->shouldReceive('toArray')->with()->andReturn(
            [
                'first_name' => 'Speaker Name',
                'last_name' => 'Last Name',
                'company' => '',
                'twitter' => '',
                'info' => 'My information',
                'bio' => 'Interesting details about my life',
                'photo_path' => '',
                'url' => '',
                'airport' => '',
                'transportation' => '',
                'hotel' => '',
            ]
        );

        unset($this->app['spot']);
        $this->app['spot'] = $spot;

        $response = $controller->editAction($this->req);

        $this->assertInstanceOf(
            'Symfony\Component\HttpFoundation\Response',
            $response
        );
        $this->assertContains('<h2 class="headline">My Profile</h2>', (string) $response);
        $this->assertContains('Interesting details about my life', (string) $response);
    }

    /**
     * @test
     */
    public function notAbleToEditOtherPersonsProfile()
    {
        $this->req->shouldReceive('get')->with('id')->andReturn('2');

        $controller = new ProfileController();
        $controller->setApplication($this->app);

        $response = $controller->processAction($this->req);

        $flash= $this->app['session']->get('flash');

        $this->assertInstanceOf(
            'Symfony\Component\HttpFoundation\RedirectResponse',
            $response
        );
        $this->assertEquals('error', $flash['type']);
        $this->assertEquals("You cannot edit someone else's profile", $flash['ext']);
        $this->assertContains('dashboard', $response->getTargetUrl());
    }

    /**
     * @test
     */
    public function canNotUpdateProfileWithInvalidData()
    {
        $this->putUserInRequest(false);

        $controller = new ProfileController();
        $controller->setApplication($this->app);
        $response = $controller->processAction($this->req);

        $this->assertInstanceOf(
            'Symfony\Component\HttpFoundation\Response',
            $response
        );
        $this->assertContains('Invalid email address format', (string) $response);
        $this->assertContains('<h2 class="headline">My Profile</h2>', (string) $response);
        $this->assertContains('<label for="form-user-email">Email</label>', (string) $response);
    }

    /**
     * @test
     */
    public function redirectToDashboardOnSuccessfulUpdate()
    {
        $this->putUserInRequest(true);

        $user = m::mock('StdClass');

        $spot = m::mock(Locator::class);
        $spot->shouldReceive('mapper')->with('\OpenCFP\Domain\Entity\User')->andReturn($spot);
        $spot->shouldReceive('get')
            ->with($this->app[Authentication::class]->user()->getId())
            ->andReturn($user);
        $spot->shouldReceive('save')->with($user)->andReturn(0);

        unset($this->app['spot']);
        $this->app['spot'] = $spot;

        $controller = new ProfileController();
        $controller->setApplication($this->app);
        $response = $controller->processAction($this->req);

        $flash= $this->app['session']->get('flash');

        $this->assertInstanceOf(
            'Symfony\Component\HttpFoundation\RedirectResponse',
            $response
        );
        $this->assertContains('dashboard', $response->getTargetUrl());
        $this->assertEquals('success', $flash['type']);
        $this->assertEquals('Successfully updated your information!', $flash['ext']);
    }

    /**
     * @test
     */
    public function displayChangePasswordWhenAllowed()
    {
        $controller = new ProfileController();
        $controller->setApplication($this->app);
        $response = $controller->passwordAction($this->req);

        $this->assertInstanceOf(
            'Symfony\Component\HttpFoundation\Response',
            $response
        );
        $this->assertContains(
            '<h2 class="headline">Change Your Password</h2>',
            (string) $response
        );
    }

    /**
     * Helper function to fake a user in the request object.
     *
     * @param $isEmailValid bool whether or not to use a valid email address
     */
    private function putUserInRequest($isEmailValid)
    {
        $email = $isEmailValid ? 'valideamial@cfp.org' : 'invalidEmail';

        $this->req->shouldReceive('get')->with('id')->andReturn('1');
        $this->req->shouldReceive('get')->with('email')->andReturn($email);
        $this->req->shouldReceive('get')->with('first_name')->andReturn('My Name');
        $this->req->shouldReceive('get')->with('last_name')->andReturn('The Second');
        $this->req->shouldReceive('get')->with('company')->andReturn('');
        $this->req->shouldReceive('get')->with('twitter')->andReturn('');
        $this->req->shouldReceive('get')->with('airport')->andReturn('');
        $this->req->shouldReceive('get')->with('transportation')->andReturn('');
        $this->req->shouldReceive('get')->with('hotel')->andReturn('');
        $this->req->shouldReceive('get')->with('url')->andReturn('https://joind.in/user/myname');
        $this->req->shouldReceive('get')->with('speaker_info')->andReturn('All my info');
        $this->req->shouldReceive('get')->with('speaker_bio')->andReturn('I did a lot of things');
        $this->req->shouldReceive('get')->with('speaker_photo')->andReturn('');
        $this->req->files = $this->req;
    }
}
