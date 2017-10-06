<?php

namespace OpenCFP\Test\Http\Controller;

use Cartalyst\Sentry\Users\UserInterface;
use HTMLPurifier;
use HTMLPurifier_Config;
use Mockery as m;
use OpenCFP\Application;
use OpenCFP\Domain\CallForProposal;
use OpenCFP\Domain\Services\AccountManagement;
use OpenCFP\Domain\Services\Authentication;
use OpenCFP\Environment;
use Spot\Locator;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockFileSessionStorage;

class SignupControllerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @test
     * @dataProvider badSignupDateProvider
     */
    public function signupAfterEnddateShowsError($endDateString, $currentTimeString)
    {
        // report that there is no active user
        $auth = m::mock(Authentication::class);
        $auth->shouldReceive('check')->andReturn(false);

        $app = m::mock(\OpenCFP\Application::class);
        // Create a session
        $app->shouldReceive('redirect');

        $app->shouldReceive('offsetGet')->with(Authentication::class)->andReturn($auth);
        $app->shouldReceive('config')->with('application.enddate')->andReturn($endDateString);

        // Create a session
        $app->shouldReceive('offsetGet')->with('session')->andReturn(new Session(new MockFileSessionStorage()));


        // Create our URL generator
        $url = 'http://opencfp/signup';
        $url_generator = m::mock('Symfony\Component\Routing\Generator\UrlGeneratorInterface');
        $url_generator->shouldReceive('generate')->andReturn($url);
        $app->shouldReceive('offsetGet')->with('url_generator')->andReturn($url_generator);

        $app->shouldReceive('offsetGet')->with('callforproposal')->andReturn(
            new CallForProposal(new \DateTime($endDateString))
        );

        $controller = new \OpenCFP\Http\Controller\SignupController();
        $controller->setApplication($app);

        $req = m::mock('Symfony\Component\HttpFoundation\Request');
        $controller->indexAction($req, $currentTimeString);

        $expectedMessage = 'Sorry, the call for papers has ended.';
        $session_details = $app['session']->get('flash');

        $this->assertContains(
            $expectedMessage,
            $session_details['ext'],
            'Did not get cfp closed message'
        );
    }

    /**
     * @test
     * @dataProvider goodSignupDateProvider
     */
    public function signupBeforeEnddateRendersSignupForm($endDateString, $currentTimeString)
    {
        $app = new Application(BASE_PATH, Environment::testing());
        $app['session.test'] = true;

        // set the application end date configuration
        $config = $app['config'];
        $config['application']['enddate'] = $endDateString;
        $app['config'] = $config;

        // report that there is no active user
        $auth = m::mock(Authentication::class);
        $auth->shouldReceive('check')->andReturn(false);
        $app[Authentication::class] = $auth;

        $app['callforproposal'] = new CallForProposal(
            new \DateTime($endDateString)
        );

        ob_start();
        $app->run();
        ob_end_clean();

        $controller = new \OpenCFP\Http\Controller\SignupController();
        $controller->setApplication($app);

        $req = m::mock('Symfony\Component\HttpFoundation\Request');
        $response = $controller->indexAction($req, $currentTimeString);

        // Make sure we see the signup page
        $this->assertContains(
            '<!-- page-id: user/create -->',
            (string) $response
        );
    }

    public function badSignupDateProvider()
    {
        return [
            [$close = 'Jan 1, 2000', $now = 'Jan 2, 2000'],
        ];
    }

    public function goodSignupDateProvider()
    {
        return [
            [$close = 'Jan 1, 2000', $now = 'Jan 1, 2000 3:00 PM'],
            ['Jan 2, 2000', 'Jan 1, 2000'],
        ];
    }

    /**
     * @test
     */
    public function signupWithValidInfoWorks()
    {
        $app = m::mock(\OpenCFP\Application::class);
        $app->shouldReceive('redirect');

        // Create a session
        $app->shouldReceive('offsetGet')->with('session')->andReturn(new Session(new MockFileSessionStorage()));

        // Create our URL generator
        $url = 'http://opencfp/signup';
        $url_generator = m::mock('Symfony\Component\Routing\Generator\UrlGeneratorInterface');
        $url_generator->shouldReceive('generate')->andReturn($url);
        $app->shouldReceive('offsetGet')->with('url_generator')->andReturn($url_generator);

        // We need to set up our speaker information
        $form_data = [
            'formAction' => $url,
            'first_name' => 'Testy',
            'last_name' => 'McTesterton',
            'email' => 'test@opencfp.org',
            'company' => null,
            'twitter' => null,
            'url' => 'https://joind.in/user/abc123',
            'password' => 'wutwut',
            'password2' => 'wutwut',
            'airport' => null,
            'speaker_info' => null,
            'speaker_bio' => null,
            'transportation' => null,
            'hotel' => null,
            'buttonInfo' => 'Create my speaker profile',
            'agree_coc' => null,
        ];

        // Set our HTMLPurifier we use for validation
        $config = HTMLPurifier_Config::createDefault();
        $purifier = new HTMLPurifier($config);
        $app->shouldReceive('offsetGet')->with('purifier')->andReturn($purifier);
        $app->shouldReceive('config')->with('application.coc_link')->andReturn(null);

        $user = m::mock(UserInterface::class);
        $user->id = 1;

        $auth = m::mock(Authentication::class);
        $auth->shouldReceive('user')->andReturn($user);
        $app->shouldReceive('offsetGet')->with(Authentication::class)->andReturn($auth);

        $accounts = m::mock(AccountManagement::class);
        $accounts->shouldReceive('create')->andReturn($user);
        $app->shouldReceive('offsetGet')->with(AccountManagement::class)->andReturn($accounts);

        // Create an instance of our database
        $speaker = new \stdClass;
        $mapper = m::mock('stdClass');
        $mapper->shouldReceive('get')->andReturn($speaker);
        $mapper->shouldReceive('save');
        $spot = m::mock(Locator::class);
        $spot->shouldReceive('mapper')->andReturn($mapper);
        $app->shouldReceive('offsetGet')->with('spot')->andReturn($spot);

        // Create an instance of the controller and we're all set
        $controller = new \OpenCFP\Http\Controller\SignupController();
        $controller->setApplication($app);

        $req = m::mock('Symfony\Component\HttpFoundation\Request');


        foreach ($form_data as $field => $value) {
            $req->shouldReceive('get')->with($field)->andReturn($value);
        }

        $files = m::mock('StdClass');
        $files->shouldReceive('get')->with('speaker_photo')->andReturn(null);
        $req->files = $files;

        $controller->processAction($req, $app);
        $expectedMessage = "You've successfully created your account!";
        $session_details = $app['session']->get('flash');

        $this->assertContains(
            $expectedMessage,
            $session_details['ext'],
            'Did not successfully create an account'
        );
    }

    /**
     * @test
     */
    public function signupWithOutJoindInWorks()
    {
        $app = m::mock(\OpenCFP\Application::class);
        $app->shouldReceive('redirect');

        // Create a session
        $app->shouldReceive('offsetGet')->with('session')->andReturn(new Session(new MockFileSessionStorage()));

        // Create our URL generator
        $url = 'http://opencfp/signup';
        $url_generator = m::mock('Symfony\Component\Routing\Generator\UrlGeneratorInterface');
        $url_generator->shouldReceive('generate')->andReturn($url);
        $app->shouldReceive('offsetGet')->with('url_generator')->andReturn($url_generator);

        // We need to set up our speaker information
        $form_data = [
            'formAction' => 'http://opencfp/signup',
            'first_name' => 'Testy',
            'last_name' => 'McTesterton',
            'email' => 'test@opencfp.org',
            'company' => null,
            'twitter' => null,
            'url' => '',
            'password' => 'wutwut',
            'password2' => 'wutwut',
            'airport' => null,
            'speaker_info' => null,
            'speaker_bio' => null,
            'transportation' => null,
            'hotel' => null,
            'buttonInfo' => 'Create my speaker profile',
            'agree_coc' => null,
        ];

        // Set our HTMLPurifier we use for validation
        $config = HTMLPurifier_Config::createDefault();
        $purifier = new HTMLPurifier($config);
        $app->shouldReceive('offsetGet')->with('purifier')->andReturn($purifier);
        $app->shouldReceive('config')->with('application.coc_link')->andReturn(null);

        $user = m::mock(UserInterface::class);
        $user->id = 1;

        $accounts = m::mock(AccountManagement::class);
        $accounts->shouldReceive('create')->andReturn($user);

        $app->shouldReceive('offsetGet')->with(AccountManagement::class)->andReturn($accounts);

        // Create an instance of our database
        $speaker = new \stdClass;
        $mapper = m::mock('stdClass');
        $mapper->shouldReceive('get')->andReturn($speaker);
        $mapper->shouldReceive('save');
        $spot = m::mock(Locator::class);
        $spot->shouldReceive('mapper')->andReturn($mapper);
        $app->shouldReceive('offsetGet')->with('spot')->andReturn($spot);

        // Create an instance of the controller and we're all set
        $controller = new \OpenCFP\Http\Controller\SignupController();
        $controller->setApplication($app);

        $req = m::mock('Symfony\Component\HttpFoundation\Request');


        foreach ($form_data as $field => $value) {
            $req->shouldReceive('get')->with($field)->andReturn($value);
        }

        $files = m::mock('StdClass');
        $files->shouldReceive('get')->with('speaker_photo')->andReturn(null);
        $req->files = $files;

        $controller->processAction($req, $app);
        $expectedMessage = "You've successfully created your account!";
        $session_details = $app['session']->get('flash');

        $this->assertContains(
            $expectedMessage,
            $session_details['ext'],
            'Did not successfully create an account'
        );
    }

    /**
     * @test
     */
    public function signupCocWithValidInfoWorks()
    {
        $app = m::mock(\OpenCFP\Application::class);
        $app->shouldReceive('redirect');

        // Create a session
        $app->shouldReceive('offsetGet')->with('session')->andReturn(new Session(new MockFileSessionStorage()));

        // Create our URL generator
        $url = 'http://opencfp/signup';
        $url_generator = m::mock('Symfony\Component\Routing\Generator\UrlGeneratorInterface');
        $url_generator->shouldReceive('generate')->andReturn($url);
        $app->shouldReceive('offsetGet')->with('url_generator')->andReturn($url_generator);

        // We need to set up our speaker information
        $form_data = [
            'formAction' => $url,
            'first_name' => 'Testy',
            'last_name' => 'McTesterton',
            'email' => 'test@opencfp.org',
            'company' => null,
            'twitter' => null,
            'url' => 'https://joind.in/user/abc123',
            'password' => 'wutwut',
            'password2' => 'wutwut',
            'airport' => null,
            'speaker_info' => null,
            'speaker_bio' => null,
            'transportation' => null,
            'hotel' => null,
            'buttonInfo' => 'Create my speaker profile',
            'agree_coc' => 'agreed',
        ];

        // Set our HTMLPurifier we use for validation
        $config = HTMLPurifier_Config::createDefault();
        $purifier = new HTMLPurifier($config);
        $app->shouldReceive('offsetGet')->with('purifier')->andReturn($purifier);
        $app->shouldReceive('config')->with('application.coc_link')->andReturn('http://www.google.com');

        $user = m::mock(UserInterface::class);
        $user->id = 1;

        $accounts = m::mock(AccountManagement::class);
        $accounts->shouldReceive('create')->andReturn($user);

        $app->shouldReceive('offsetGet')->with(AccountManagement::class)->andReturn($accounts);

        // Create an instance of our database
        $speaker = new \stdClass;
        $mapper = m::mock('stdClass');
        $mapper->shouldReceive('get')->andReturn($speaker);
        $mapper->shouldReceive('save');
        $spot = m::mock(Locator::class);
        $spot->shouldReceive('mapper')->andReturn($mapper);
        $app->shouldReceive('offsetGet')->with('spot')->andReturn($spot);

        // Create an instance of the controller and we're all set
        $controller = new \OpenCFP\Http\Controller\SignupController();
        $controller->setApplication($app);

        $req = m::mock('Symfony\Component\HttpFoundation\Request');


        foreach ($form_data as $field => $value) {
            $req->shouldReceive('get')->with($field)->andReturn($value);
        }

        $files = m::mock('StdClass');
        $files->shouldReceive('get')->with('speaker_photo')->andReturn(null);
        $req->files = $files;

        $controller->processAction($req, $app);
        $expectedMessage = "You've successfully created your account!";
        $session_details = $app['session']->get('flash');

        $this->assertContains(
            $expectedMessage,
            $session_details['ext'],
            'Did not successfully create an account'
        );
    }
}
