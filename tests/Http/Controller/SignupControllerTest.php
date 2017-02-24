<?php

namespace OpenCFP\Test\Http\Controller;

use HTMLPurifier;
use HTMLPurifier_Config;
use Mockery as m;
use OpenCFP\Application;
use OpenCFP\Domain\CallForProposal;
use OpenCFP\Environment;
use OpenCFP\Http\Controller\SignupController;
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
        $app = m::mock(Application::class);
        $app->shouldReceive('redirect');

        // Set our enddate for the CfP
        $app->shouldReceive('config')->with('application.enddate')->andReturn($endDateString);

        // Create our Sentinel double as a user is not logged in
        $sentinel = m::mock(Sentinel::class);
        $sentinel->shouldReceive('check')->andReturn(false);
        $app->shouldReceive('offsetGet')->with('sentinel')->andReturn($sentinel);

        // Create a session
        $app->shouldReceive('offsetGet')->with('session')->andReturn(new Session(new MockFileSessionStorage()));

        // Create our URL generator
        $url = 'http://opencfp/signup';
        $url_generator = m::mock('Symfony\Component\Routing\Generator\UrlGeneratorInterface');
        $url_generator->shouldReceive('generate')->andReturn($url);
        $app->shouldReceive('offsetGet')->with('url_generator')->andReturn($url_generator);

        // Set our CallForProposal service the way we want
        $app->shouldReceive('offsetGet')->with('callforproposal')->andReturn(
            new CallForProposal(new \DateTime($endDateString))
        );

        $controller = new \OpenCFP\Http\Controller\SignupController();
        $controller->setApplication($app);

        $req = m::mock('Symfony\Component\HttpFoundation\Request');
        $controller->indexAction($req, $currentTimeString);

        $expectedMessage = "Sorry, the call for papers has ended.";
        $session_details = $app['session']->get('flash');

        $this->assertContains(
            $expectedMessage,
            $session_details['ext'],
            "Did not get cfp closed message"
        );
    }

    /**
     * @test
     * @dataProvider goodSignupDateProvider
     */
    public function signupBeforeEnddateRendersSignupForm($endDateString, $currentTimeString)
    {
        // Create our test-centric App object
        $app = new Application(BASE_PATH, Environment::testing());

        // set the application end date configuration
        $config = $app['config'];
        $config['application']['enddate'] = $endDateString;
        $app['config'] = $config;

        // Override our CallForProposal's object
        $app['callforproposal'] = new CallForProposal(
            new \DateTime($endDateString)
        );

        // Create our Sentinel double
        $sentinel = m::mock('SentinelWrapper');

        // User is not logged in
        $sentinel->shouldReceive('check')->andReturn(false);
        $app['sentinel'] = $sentinel;
        $app['session'] = new Session(new MockFileSessionStorage());
        $app['session.test'] = true;

        // Fire up our Application object
        ob_start();
        $app->run();
        ob_end_clean();

        $controller = new SignupController();
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

    public function formDataProvider()
    {
        return [
            [[
                'formAction' => 'http://opencfp/signup',
                'first_name' => 'Testy',
                'last_name' => 'McTesterton',
                'email' => 'test@opencfp.org',
                'company' => null,
                'twitter' => null,
                'password' => 'wutwut',
                'password2' => 'wutwut',
                'airport' => null,
                'speaker_info' => null,
                'speaker_bio' => null,
                'transportation' => null,
                'hotel' => null,
                'buttonInfo' => 'Create my speaker profile',
                'agree_coc' => null,
            ]],
            [[
                'formAction' => 'http://opencfp/signup',
                'first_name' => 'Testy',
                'last_name' => 'McTesterton',
                'email' => 'test@opencfp.org',
                'company' => null,
                'twitter' => null,
                'password' => 'wutwut',
                'password2' => 'wutwut',
                'airport' => null,
                'speaker_info' => null,
                'speaker_bio' => null,
                'transportation' => null,
                'hotel' => null,
                'buttonInfo' => 'Create my speaker profile',
                'agree_coc' => 'agreed'
            ]],
        ];
    }

    /**
     * @test
     * @dataProvider formDataProvider
     */
    public function signupWithValidInfoWorks($form_data)
    {
        $app = m::mock(\OpenCFP\Application::class);
        $app->shouldReceive('redirect');

        // Create a pretend Sentry object that registers and activates our user
        $user = m::mock('stdClass');
        $user->shouldReceive('attach');
        $sentinel = m::mock('OpenCFP\Util\Wrapper\SentinelWrapper');
        $sentinel->shouldReceive('registerAndActivate')->andReturn($user);
        $role = m::mock('stdClass');
        $role->shouldReceive('users')->andReturn($user);
        $sentinel->shouldReceive('findRoleBySlug')->with('speaker')->andReturn($role);
        $app->shouldReceive('offsetGet')->with('sentinel')->andReturn($sentinel);

        // Create a session
        $app->shouldReceive('offsetGet')->with('session')->andReturn(new Session(new MockFileSessionStorage()));

        // Get our form factory and have it return our form
        $isValid = true;
        $app->shouldReceive('offsetGet')->with('form.factory')->andReturn($this->createSingupFormFactory($isValid));

        // Create our URL generator
        $url = 'http://opencfp/signup';
        $url_generator = m::mock('Symfony\Component\Routing\Generator\UrlGeneratorInterface');
        $url_generator->shouldReceive('generate')->andReturn($url);
        $app->shouldReceive('offsetGet')->with('url_generator')->andReturn($url_generator);

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
        $expectedMessage = "Your account has been created, you're ready to log in";
        $session_details = $app['session']->get('flash');

        $this->assertContains(
            $expectedMessage,
            $session_details['ext'],
            "Did not successfully create an account"
        );
    }

    protected function createSingupFormFactory($isValid)
    {
        $signup_form = m::mock('OpenCFP\Http\Form\SignupForm');
        $signup_form->shouldReceive('handleRequest');
        $signup_form->shouldReceive('getData');
        $signup_form->shouldReceive('isValid')->andReturn($isValid);
        $form_factory = m::mock('stdClass');
        $form_factory->shouldReceive('createBuilder->getForm')->andReturn($signup_form);

        return $form_factory;
    }
}
