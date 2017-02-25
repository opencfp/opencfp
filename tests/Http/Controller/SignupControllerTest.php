<?php

namespace OpenCFP\Test\Http\Controller;

use Mockery as m;
use OpenCFP\Application;
use OpenCFP\Domain\CallForProposal;
use OpenCFP\Environment;
use OpenCFP\Http\Controller\SignupController;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockFileSessionStorage;

class SignupControllerTest extends \PHPUnit\Framework\TestCase
{
    public function tearDown()
    {
        m::close();
    }

    /**
     * @test
     * @dataProvider badSignupDateProvider
     */
    public function signupAfterEnddateShowsError($endDateString, $currentTimeString)
    {
        // Create our test-centric App object
        $app = new Application(BASE_PATH, Environment::testing());

        // Set our enddate for the CfP
        $config = $app['config'];
        $config['application']['enddate'] = $endDateString;
        $app['config'] = $config;

        // Create our Sentinel double as a user is not logged in
        $sentinel = m::mock(Sentinel::class);
        $sentinel->shouldReceive('check')->andReturn(false);
        $app['sentinel'] = $sentinel;

        // Override the OpenCFP object we have
        $app['callforproposal'] = new CallForProposal(new \DateTime($endDateString));

        // User is not logged in
        $sentinel->shouldReceive('check')->andReturn(false);
        $app['sentinel'] = $sentinel;
        $app['session'] = new Session(new MockFileSessionStorage());
        $app['session.test'] = true;

        ob_start();
        $app->run();
        ob_end_clean();

        $controller = new \OpenCFP\Http\Controller\SignupController();
        $controller->setApplication($app);

        $req = m::mock('Symfony\Component\HttpFoundation\Request');
        $response = $controller->indexAction($req, $currentTimeString);

        // Make sure we are being redirected to the home page
        $this->assertContains(
            '<meta http-equiv="refresh" content="1;url=/" />',
            (string) $response
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

        // Create a double for call for proposal
        $app['callforproposal'] = new CallForProposal(new \DateTimeImmutable($endDateString));

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

        // Make sure we are being shown the user signup page
        $this->assertContains(
            '!-- page-id: user/create -->',
            (string) $response
        );
    }

    public function badSignupDateProvider()
    {
        // end date, current date
        return [
            [$close = 'Jan 1, 2000', $now = 'Jan 2, 2000']
        ];
    }

    public function goodSignupDateProvider()
    {
        // end date, current date
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
        // Create our test-centric App object
        $app = new Application(BASE_PATH, Environment::testing());

        // Set up our form factory with a double
        $app['form.factory'] = $this->createSignupFormFactory(
            true,
            ['email' => 'test@opencfp.org', 'password' => 'wutwut', 'first_name' => 'Test']
        );

        // Update the config to remove that we need a Code of Conduct link
        $config = $app['config'];
        unset($config['application']['coc_link']);
        $app['config'] = $config;

        // Set our session into test mode
        $app['session'] = new Session(new MockFileSessionStorage());
        $app['session.test'] = true;

        // Create our Sentinel double
        $user = new \stdClass;
        $role = m::mock('stdClass');
        $role->shouldReceive('users->attach')->with($user);
        $sentinel = m::mock('\OpenCFP\Util\Wrapper\SentinelWrapper')->makePartial();
        $sentinel->shouldReceive('registerAndActivate')->andReturn($user);
        $sentinel->shouldReceive('findRoleBySlug')->with('speaker')->andReturn($role);
        $app['sentinel'] = $sentinel;

        // Fire up our Application object
        ob_start();
        $app->run();
        ob_end_clean();

        // Create our Request double
        $req = m::mock('Symfony\Component\HttpFoundation\Request');
        $req->shouldReceive('getMethod')->andReturn('post');

        $controller = new SignupController();
        $controller->setApplication($app);
        $response = $controller->processAction($req, $app);

        // Make sure we see that we are being redirected to the login page
        $this->assertContains(
            '<meta http-equiv="refresh" content="1;url=/login" />',
            (string) $response
        );
    }

    protected function createSignupFormFactory($isValid, $user_data)
    {
        // First create our UserEntity
        $user_entity = m::mock('OpenCFP\Http\Form\Entity\User')->makePartial();
        $user_entity->shouldReceive('getEmail')->andReturn($user_data['email']);
        $user_entity->shouldReceive('getPassword')->andReturn($user_data['password']);

        // Then create our form
        $signup_form = m::mock('OpenCFP\Http\Form\SignupForm')->makePartial();
        $signup_form->shouldReceive('handleRequest');
        $signup_form->shouldReceive('getData')->andReturn($user_entity);
        $signup_form->shouldReceive('isValid')->andReturn($isValid);

        // Finally the form factory returns our doubled form
        $form_factory = m::mock('stdClass');
        $form_factory->shouldReceive('createBuilder->getForm')->andReturn($signup_form);

        return $form_factory;
    }
}
