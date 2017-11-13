<?php

namespace OpenCFP\Test\Http\Controller;

use Cartalyst\Sentry\Users\UserInterface;
use HTMLPurifier;
use HTMLPurifier_Config;
use Mockery as m;
use OpenCFP\Domain\Services\AccountManagement;
use OpenCFP\Domain\Services\Authentication;
use OpenCFP\Test\WebTestCase;
use Spot\Locator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockFileSessionStorage;

/**
 * Class SignupControllerTest
 *
 * @package OpenCFP\Test\Http\Controller
 * @group db
 */
class SignupControllerTest extends WebTestCase
{
    /**
     * @test
     */
    public function signupAfterEnddateShowsError()
    {
        $this->callForPapersIsClosed()->get('/signup')
            ->assertRedirect()
            ->assertNotSee('Signup');
    }

    /**
     * @test
     */
    public function signupBeforeEnddateRendersSignupForm()
    {
        $this->callForPapersIsOpen()->get('/signup')
            ->assertSuccessful()
            ->assertSee('Signup');
    }

    /**
     * @test
     */
    public function signUpRedirectsWhenLoggedIn()
    {
        $this->asAdmin()->get('/signup')
            ->assertRedirect()
            ->assertNotSee('Signup');
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
        $url           = 'http://opencfp/signup';
        $url_generator = m::mock(\Symfony\Component\Routing\Generator\UrlGeneratorInterface::class);
        $url_generator->shouldReceive('generate')->andReturn($url);
        $app->shouldReceive('offsetGet')->with('url_generator')->andReturn($url_generator);

        // We need to set up our speaker information
        $form_data = [
            'formAction'     => $url,
            'first_name'     => 'Testy',
            'last_name'      => 'McTesterton',
            'email'          => 'test@opencfp.org',
            'company'        => null,
            'twitter'        => null,
            'url'            => 'https://joind.in/user/abc123',
            'password'       => 'wutwut',
            'password2'      => 'wutwut',
            'airport'        => null,
            'speaker_info'   => null,
            'speaker_bio'    => null,
            'transportation' => null,
            'hotel'          => null,
            'buttonInfo'     => 'Create my speaker profile',
            'agree_coc'      => null,
        ];

        // Set our HTMLPurifier we use for validation
        $config   = HTMLPurifier_Config::createDefault();
        $purifier = new HTMLPurifier($config);
        $app->shouldReceive('offsetGet')->with('purifier')->andReturn($purifier);
        $app->shouldReceive('config')->with('application.coc_link')->andReturn(null);

        $user     = m::mock(UserInterface::class);
        $user->id = 1;

        $auth = m::mock(Authentication::class);
        $auth->shouldReceive('user')->andReturn($user);
        $auth->shouldReceive('authenticate')->andReturn(true);
        $app->shouldReceive('offsetGet')->with(Authentication::class)->andReturn($auth);

        $accounts = m::mock(AccountManagement::class);
        $accounts->shouldReceive('create')->andReturn($user);
        $app->shouldReceive('offsetGet')->with(AccountManagement::class)->andReturn($accounts);

        // Create an instance of our database
        $speaker = new \stdClass;
        $mapper  = m::mock(\stdClass::class);
        $mapper->shouldReceive('get')->andReturn($speaker);
        $mapper->shouldReceive('save');
        $spot = m::mock(Locator::class);
        $spot->shouldReceive('mapper')->andReturn($mapper);
        $app->shouldReceive('offsetGet')->with('spot')->andReturn($spot);

        $request = Request::create('/signup', 'POST', [
            'email'    => 'test@example.com',
            'password' => 'pa$$w3rd',
            'coc'      => '1',
        ]);

        $requestStack = m::mock(\stdClass::class);
        $requestStack->shouldReceive('getCurrentRequest')->andReturn($request);
        $app->shouldReceive('offsetGet')->with('request_stack')->andReturn($requestStack);

        // Create an instance of the controller and we're all set
        $controller = new \OpenCFP\Http\Controller\SignupController();
        $controller->setApplication($app);

        $req = m::mock(\Symfony\Component\HttpFoundation\Request::class);

        foreach ($form_data as $field => $value) {
            $req->shouldReceive('get')->with($field)->andReturn($value);
        }

        $files = m::mock(\stdClass::class);
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
        $url           = 'http://opencfp/signup';
        $url_generator = m::mock(\Symfony\Component\Routing\Generator\UrlGeneratorInterface::class);
        $url_generator->shouldReceive('generate')->andReturn($url);
        $app->shouldReceive('offsetGet')->with('url_generator')->andReturn($url_generator);

        // We need to set up our speaker information
        $form_data = [
            'formAction'     => 'http://opencfp/signup',
            'first_name'     => 'Testy',
            'last_name'      => 'McTesterton',
            'email'          => 'test@opencfp.org',
            'company'        => null,
            'twitter'        => null,
            'url'            => '',
            'password'       => 'wutwut',
            'password2'      => 'wutwut',
            'airport'        => null,
            'speaker_info'   => null,
            'speaker_bio'    => null,
            'transportation' => null,
            'hotel'          => null,
            'buttonInfo'     => 'Create my speaker profile',
            'agree_coc'      => null,
        ];

        // Set our HTMLPurifier we use for validation
        $config   = HTMLPurifier_Config::createDefault();
        $purifier = new HTMLPurifier($config);
        $app->shouldReceive('offsetGet')->with('purifier')->andReturn($purifier);
        $app->shouldReceive('config')->with('application.coc_link')->andReturn(null);

        $user     = m::mock(UserInterface::class);
        $user->id = 1;

        $accounts = m::mock(AccountManagement::class);
        $accounts->shouldReceive('create')->andReturn($user);

        $app->shouldReceive('offsetGet')->with(AccountManagement::class)->andReturn($accounts);

        $auth = m::mock(Authentication::class);
        $auth->shouldReceive('user')->andReturn($user);
        $auth->shouldReceive('authenticate')->andReturn(true);
        $app->shouldReceive('offsetGet')->with(Authentication::class)->andReturn($auth);

        // Create an instance of our database
        $speaker = new \stdClass;
        $mapper  = m::mock(\stdClass::class);
        $mapper->shouldReceive('get')->andReturn($speaker);
        $mapper->shouldReceive('save');
        $spot = m::mock(Locator::class);
        $spot->shouldReceive('mapper')->andReturn($mapper);
        $app->shouldReceive('offsetGet')->with('spot')->andReturn($spot);

        $request = Request::create('/signup', 'POST', [
            'email'    => 'test@example.com',
            'password' => 'pa$$w3rd',
            'coc'      => '1',
        ]);

        $requestStack = m::mock(\stdClass::class);
        $requestStack->shouldReceive('getCurrentRequest')->andReturn($request);
        $app->shouldReceive('offsetGet')->with('request_stack')->andReturn($requestStack);

        // Create an instance of the controller and we're all set
        $controller = new \OpenCFP\Http\Controller\SignupController();
        $controller->setApplication($app);

        $req = m::mock(\Symfony\Component\HttpFoundation\Request::class);

        foreach ($form_data as $field => $value) {
            $req->shouldReceive('get')->with($field)->andReturn($value);
        }

        $files = m::mock(\stdClass::class);
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
        $url           = 'http://opencfp/signup';
        $url_generator = m::mock(\Symfony\Component\Routing\Generator\UrlGeneratorInterface::class);
        $url_generator->shouldReceive('generate')->andReturn($url);
        $app->shouldReceive('offsetGet')->with('url_generator')->andReturn($url_generator);

        // We need to set up our speaker information
        $form_data = [
            'formAction'     => $url,
            'first_name'     => 'Testy',
            'last_name'      => 'McTesterton',
            'email'          => 'test@opencfp.org',
            'company'        => null,
            'twitter'        => null,
            'url'            => 'https://joind.in/user/abc123',
            'password'       => 'wutwut',
            'password2'      => 'wutwut',
            'airport'        => null,
            'speaker_info'   => null,
            'speaker_bio'    => null,
            'transportation' => null,
            'hotel'          => null,
            'buttonInfo'     => 'Create my speaker profile',
            'agree_coc'      => 'agreed',
        ];

        // Set our HTMLPurifier we use for validation
        $config   = HTMLPurifier_Config::createDefault();
        $purifier = new HTMLPurifier($config);
        $app->shouldReceive('offsetGet')->with('purifier')->andReturn($purifier);
        $app->shouldReceive('config')->with('application.coc_link')->andReturn('http://www.google.com');

        $user     = m::mock(UserInterface::class);
        $user->id = 1;

        $accounts = m::mock(AccountManagement::class);
        $accounts->shouldReceive('create')->andReturn($user);

        $app->shouldReceive('offsetGet')->with(AccountManagement::class)->andReturn($accounts);

        // Create an instance of our database
        $speaker = new \stdClass;
        $mapper  = m::mock(\stdClass::class);
        $mapper->shouldReceive('get')->andReturn($speaker);
        $mapper->shouldReceive('save');
        $spot = m::mock(Locator::class);
        $spot->shouldReceive('mapper')->andReturn($mapper);
        $app->shouldReceive('offsetGet')->with('spot')->andReturn($spot);

        $auth = m::mock(Authentication::class);
        $auth->shouldReceive('user')->andReturn($user);
        $auth->shouldReceive('authenticate')->andReturn(true);
        $app->shouldReceive('offsetGet')->with(Authentication::class)->andReturn($auth);

        $request = Request::create('/signup', 'POST', [
            'email'    => 'test@example.com',
            'password' => 'pa$$w3rd',
            'coc'      => '1',
        ]);

        $requestStack = m::mock(\stdClass::class);
        $requestStack->shouldReceive('getCurrentRequest')->andReturn($request);
        $app->shouldReceive('offsetGet')->with('request_stack')->andReturn($requestStack);

        // Create an instance of the controller and we're all set
        $controller = new \OpenCFP\Http\Controller\SignupController();
        $controller->setApplication($app);

        $req = m::mock(\Symfony\Component\HttpFoundation\Request::class);

        foreach ($form_data as $field => $value) {
            $req->shouldReceive('get')->with($field)->andReturn($value);
        }

        $files = m::mock(\stdClass::class);
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
