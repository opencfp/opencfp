<?php

namespace OpenCFP\Test\Http\Controller;

use HTMLPurifier;
use HTMLPurifier_Config;
use Mockery as m;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockFileSessionStorage;

class SignupControllerTest extends \PHPUnit_Framework_TestCase
{
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
            'password' => 'wutwut',
            'password2' => 'wutwut',
            'airport' => null,
            'speaker_info' => null,
            'speaker_bio' => null,
            'transportation' => null,
            'hotel' => null,
            'buttonInfo' => 'Create my speaker profile',
        ];

        // Set our HTMLPurifier we use for validation
        $config = HTMLPurifier_Config::createDefault();
        $purifier = new HTMLPurifier($config);
        $app->shouldReceive('offsetGet')->with('purifier')->andReturn($purifier);

        // Create a pretend Sentry object that says everything is cool
        $sentry = m::mock('stdClass');
        $user = m::mock(\OpenCFP\Domain\Entity\User::class);
        $user->shouldReceive('set');
        $user->shouldReceive('addGroup');
        $user->shouldReceive('relation');
        $user->id = 1; // Any integer value is fine
        $sentry->shouldReceive('getUserProvider->create')->andReturn($user);
        $sentry->shouldReceive('getGroupProvider->findByName');

        $app->shouldReceive('offsetGet')->with('sentry')->andReturn($sentry);

        // Create an instance of our database
        $speaker = new \stdClass;
        $mapper = m::mock('stdClass');
        $mapper->shouldReceive('get')->andReturn($speaker);
        $mapper->shouldReceive('save');
        $spot = m::mock('stdClass');
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

        $response = $controller->processAction($req, $app);
        $expectedMessage = "You've successfully created your account!";
        $session_details = $app['session']->get('flash');

        $this->assertContains(
            $expectedMessage,
            $session_details['ext'],
            "Did not successfully create an account"
        );
    }
}
