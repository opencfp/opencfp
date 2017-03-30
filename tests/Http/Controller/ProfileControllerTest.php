<?php
namespace OpenCFP\Test\Http\Controller;

use Cartalyst\Sentinel\Users\EloquentUser;
use Mockery as m;
use OpenCFP\Application;
use OpenCFP\Domain\Entity\Mapper\User as UserMapper;
use OpenCFP\Domain\Entity\User;
use OpenCFP\Environment;
use OpenCFP\Http\Controller\ProfileController;
use OpenCFP\Http\Form\Entity\ChangePassword;
use OpenCFP\Http\Form\Entity\Profile;
use OpenCFP\Util\Wrapper\SentinelWrapper;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamWrapper;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockFileSessionStorage;

class ProfileControllerTest extends \PhpUnit\Framework\TestCase
{
    public $app;

    public function setUp()
    {
        $this->app = new Application(BASE_PATH, Environment::testing());
        $this->app['session'] = new Session(new MockFileSessionStorage());
        $this->app['session.test'] = true;
    }

    public function tearDown()
    {
        m::close();
    }

    public function createChangePasswordFormFactory($req, $user_id)
    {
        $change_password = new ChangePassword();
        $change_password->setUserId($user_id);
        $change_password->setPassword('passwd');
        $form = m::mock('\stdClass');
        $form->shouldReceive('handleRequest')->with($req);
        $form->shouldReceive('isValid')->andReturn(true);
        $form->shouldReceive('getData')->andReturn($change_password);
        $view = m::mock('\Symfony\Component\Form\FormView');
        $form->shouldReceive('createView')->andReturn($view);
        $form_factory = m::mock('\stdClass');
        $form_factory->shouldReceive('createBuilder->getForm')->andReturn($form);
        return $form_factory;
    }

    public function createFormFactory($req)
    {
        $profile_user = new Profile();
        $profile_user->setId(1);
        $profile_user->setEmail('test@opencfp.org');
        $profile_user->setFirstName('Test');
        $profile_user->setLastName('Test');
        $form = m::mock('\stdClass');
        $form->shouldReceive('handleRequest')->with($req);
        $form->shouldReceive('isValid')->andReturn(true);
        $form->shouldReceive('getData')->andReturn($profile_user);
        $view = m::mock('\Symfony\Component\Form\FormView');
        $form->shouldReceive('createView')->andReturn($view);
        $form_factory = m::mock('\stdClass');
        $form_factory->shouldReceive('createBuilder->getForm')->andReturn($form);
        return $form_factory;
    }

    public function createMapper($rows_updated = 1)
    {
        $user = new User();
        $mapper = m::mock(UserMapper::class);
        $mapper->shouldReceive('get')->andReturn($user);
        $mapper->shouldReceive('save')->andReturn($rows_updated);
        $spot = m::mock('\Spot\Locator');
        $spot->shouldReceive('mapper')->andReturn($mapper);
        return $spot;
    }

    public function createUser()
    {
        $user = m::mock(EloquentUser::class)->makePartial();
        $user->id = 1;
        $user->shouldReceive('hasAccess')->with('admin')->andReturn(false);
        return $user;
    }

    public function createSentinelWithLoggedInUser()
    {
        $sentinel = m::mock(SentinelWrapper::class);
        $sentinel->shouldReceive('check')->andReturn(true);
        $sentinel->shouldReceive('getUser')->andReturn($this->createUser());
        return $sentinel;
    }

    /**
     * @test
     */
    public function editKicksYouOutIfNotLoggedIn()
    {
        $sentinel = m::mock(SentinelWrapper::class);
        $sentinel->shouldReceive('check')->andReturn(false);
        $this->app['sentinel'] = $sentinel;

        ob_start();
        $this->app->run();
        ob_end_clean();

        $req = m::mock(Request::class);
        $controller = new ProfileController();
        $controller->setApp($this->app);
        $response = $controller->editAction($req);

        $this->assertContains(
            'Redirecting to /login',
            $response->getContent(),
            'User not logged in was not kicked out'
        );
    }

    /**
     * @test
     */
    public function youCannotEditSomeoneElsesProfile()
    {
        $this->app['sentinel'] = $this->createSentinelWithLoggedInUser();

        ob_start();
        $this->app->run();
        ob_end_clean();

        $req = m::mock(Request::class);
        $req->shouldReceive('get')->with('id')->andReturn(2);
        $controller = new ProfileController();
        $controller->setApp($this->app);
        $response = $controller->editAction($req);

        $this->assertContains(
            'Redirecting to /dashboard',
            $response->getContent(),
            "User was not redirected to dashboard after trying to edit someone else's profile"
        );
    }

    /**
     * @test
     */
    public function itRendersTheProfileForm()
    {
        $this->app['sentinel'] = $this->createSentinelWithLoggedInUser();

        ob_start();
        $this->app->run();
        ob_end_clean();

        $req = m::mock(Request::class);
        $req->shouldReceive('get')->with('id')->andReturn(1);
        $controller = new ProfileController();
        $controller->setApp($this->app);
        $response = $controller->editAction($req);

        $this->assertContains(
            'My Profile',
            $response->getContent(),
            'Did not display profile form correctly'
        );
    }

    /**
     * @test
     */
    public function itPreventsLoggedOutUsersPostProfileData()
    {
        $sentinel = m::mock(SentinelWrapper::class);
        $sentinel->shouldReceive('check')->andReturn(false);
        $this->app['sentinel'] = $sentinel;

        ob_start();
        $this->app->run();
        ob_end_clean();

        $req = m::mock(Request::class);
        $controller = new ProfileController();
        $controller->setApp($this->app);
        $response = $controller->processAction($req);

        $this->assertContains(
            'Redirecting to /login',
            $response->getContent(),
            'User not logged in was not kicked out'
        );
    }

    /**
     * @test
     */
    public function itHandlesInValidForms()
    {
        $this->app['sentinel'] = $this->createSentinelWithLoggedInUser();

        $req = m::mock(Request::class);
        $req->shouldReceive('getMethod')->andReturn('post');

        ob_start();
        $this->app->run();
        ob_end_clean();

        $controller = new ProfileController();
        $controller->setApp($this->app);
        $response = $controller->processAction($req);

        $this->assertContains(
            '<!-- id: user/edit -->',
            $response->getContent(),
            'Did not display form after invalid data POSTed'
        );
    }

    /**
     * @test
     */
    public function itDoesNotLetYouPostDataToSomeoneElsesProfile()
    {
        $this->app['sentinel'] = $this->createSentinelWithLoggedInUser();

        $req = m::mock(Request::class);
        $req->shouldReceive('get')->with('id')->andReturn(2);
        $profile_user = new Profile();
        $profile_user->setId(2);
        $form = m::mock('\stdClass');
        $form->shouldReceive('handleRequest')->with($req);
        $form->shouldReceive('isValid')->andReturn(true);
        $form->shouldReceive('getData')->andReturn($profile_user);
        $form_factory = m::mock('\stdClass');
        $form_factory->shouldReceive('createBuilder->getForm')->andReturn($form);
        $this->app['form.factory'] = $form_factory;

        ob_start();
        $this->app->run();
        ob_end_clean();

        $controller = new ProfileController();
        $controller->setApp($this->app);
        $response = $controller->processAction($req);

        $this->assertContains(
            'Redirecting to /dashboard',
            $response->getContent(),
            "Was allowed to POST someone else's profile data"
        );
    }

    /**
     * @test
     */
    public function validFormWithPhotoPathWorks()
    {
        $this->app['sentinel'] = $this->createSentinelWithLoggedInUser();

        $req = m::mock(Request::class);
        $req->shouldReceive('getMethod')->andReturn('post');
        $req->shouldReceive('get')->with('id')->andReturn(1);
        $profile_user = new Profile();
        $profile_user->setId(1);
        $profile_user->setEmail('test@opencfp.org');
        $profile_user->setFirstName('Test');
        $profile_user->setLastName('Test');

        // Create a virtual file system and image file
        vfsStreamWrapper::register();
        $root = vfsStream::newDirectory('images');
        vfsStreamWrapper::setRoot($root);
        $root->addChild(vfsStream::newFile('image.jpg'));
        $image_file = new UploadedFile(vfsStream::url('images/image.jpg'), 'image.jpg');
        $profile_user->setPhotoPath($image_file);

        $this->app['form.factory'] = $this->createFormFactory($req);

        // Create a double for the image processor
        $profile_image_processor = m::mock('\stdClass');
        $profile_image_processor->shouldReceive('process');
        $this->app['profile_image_processor'] = $profile_image_processor;

        $this->app['spot'] = $this->createMapper();

        ob_start();
        $this->app->run();
        ob_end_clean();

        $controller = new ProfileController();
        $controller->setApp($this->app);
        $response = $controller->processAction($req);

        $this->assertContains(
            'Redirecting to /dashboard',
            $response->getContent(),
            "Valid form with photo path didn't result in an update"
        );
    }

    /**
     * @test
     */
    public function validFormWithoutPhotoPathRedirectsToDashboard()
    {
        $this->app['sentinel'] = $this->createSentinelWithLoggedInUser();

        $req = m::mock(Request::class);
        $req->shouldReceive('getMethod')->andReturn('post');
        $req->shouldReceive('get')->with('id')->andReturn(1);
        $profile_user = new Profile();
        $profile_user->setId(1);
        $profile_user->setEmail('test@opencfp.org');
        $profile_user->setFirstName('Test');
        $profile_user->setLastName('Test');

        // Create a virtual file system and image file
        vfsStreamWrapper::register();
        $root = vfsStream::newDirectory('images');
        vfsStreamWrapper::setRoot($root);
        $root->addChild(vfsStream::newFile('image.jpg'));
        $image_file = new UploadedFile(vfsStream::url('images/image.jpg'), 'image.jpg');
        $profile_user->setPhotoPath($image_file);

        $this->app['form.factory'] = $this->createFormFactory($req);

        // Create a double for the image processor
        $profile_image_processor = m::mock('\stdClass');
        $profile_image_processor->shouldReceive('process');
        $this->app['profile_image_processor'] = $profile_image_processor;

        $this->app['spot'] = $this->createMapper();

        ob_start();
        $this->app->run();
        ob_end_clean();

        $controller = new ProfileController();
        $controller->setApp($this->app);
        $response = $controller->processAction($req);

        $this->assertContains(
            'Redirecting to /dashboard',
            $response->getContent(),
            "Valid form with photo path didn't result in an update"
        );
        $this->app['sentinel'] = $this->createSentinelWithLoggedInUser();

        $req = m::mock(Request::class);
        $req->shouldReceive('getMethod')->andReturn('post');
        $req->shouldReceive('get')->with('id')->andReturn(1);
        $this->app['form.factory'] = $this->createFormFactory($req);
        $this->app['spot'] = $this->createMapper();

        ob_start();
        $this->app->run();
        ob_end_clean();

        $controller = new ProfileController();
        $controller->setApp($this->app);
        $response = $controller->processAction($req);

        $this->assertContains(
            'Redirecting to /dashboard',
            $response->getContent(),
            "Valid form without photo path didn't result in an update"
        );
    }

    /**
     * @test
     */
    public function validFormButMapperCannotUpdateRecord()
    {
        $this->app['sentinel'] = $this->createSentinelWithLoggedInUser();

        // Let's create our form
        $req = m::mock(Request::class);
        $req->shouldReceive('get')->with('id')->andReturn(1);
        $req->shouldReceive('getMethod')->andReturn('post');

        // Create valid POST values for the form
        vfsStreamWrapper::register();
        $root = vfsStream::newDirectory('images');
        vfsStreamWrapper::setRoot($root);
        $root->addChild(vfsStream::newFile('image.jpg'));
        $image_file = new UploadedFile(vfsStream::url('images/image.jpg'), 'image.jpg');
        $req->shouldReceive('get')->with('email')->andReturn('test@opencfp.org');
        $req->shouldReceive('get')->with('first_name')->andReturn('First');
        $req->shouldReceive('get')->with('last_name')->andReturn('Last');
        $req->shouldReceive('get')->with('photo_path')->andReturn($image_file);

        // Create a double for the image processor
        $profile_image_processor = m::mock('\stdClass');
        $profile_image_processor->shouldReceive('process');
        $this->app['profile_image_processor'] = $profile_image_processor;
        $this->app['spot'] = $this->createMapper(-1);

        ob_start();
        $this->app->run();
        ob_end_clean();

        $controller = new ProfileController();
        $controller->setApp($this->app);
        $response = $controller->processAction($req);

        $this->assertContains(
            '<!-- id: user/edit -->',
            $response->getContent(),
            "Valid form with photo path didn't result in an update"
        );
    }

    /**
     * @test
     */
    public function passwordChangeRedirectsForNonAuthenticatedUser()
    {
        // Create our Request objecet
        $req = m::mock(Request::class);

        // Create a non-authenticated user
        $sentinel = m::mock(SentinelWrapper::class);
        $sentinel->shouldReceive('check')->andReturn(false);
        $this->app['sentinel'] = $sentinel;

        ob_start();
        $this->app->run();
        ob_end_clean();

        $controller = new ProfileController();
        $controller->setApp($this->app);
        $response = $controller->passwordAction($req);

        $this->assertContains(
            'Redirecting to /login',
            $response->getContent(),
            'Non-authenticated user was allowed to change their password'
        );
    }

    /**
     * @test
     */
    public function passwordChangeFormDisplayed()
    {
        // Create our Request object
        $req = m::mock(Request::class);

        $this->app['sentinel'] = $this->createSentinelWithLoggedInUser();

        ob_start();
        $this->app->run();
        ob_end_clean();

        $controller = new ProfileController();
        $controller->setApp($this->app);
        $response = $controller->passwordAction($req);

        $this->assertContains(
            '<!-- id: profile/change_password -->',
            $response->getContent(),
            'Password change form not displayed'
        );
    }

    /**
     * @test
     */
    public function processPasswordChangeRedirectsForNonAuthenticatedUser()
    {
        // Create our Request object
        $req = m::mock(Request::class);

        // Create a non-authenticated user
        $sentinel = m::mock(SentinelWrapper::class);
        $sentinel->shouldReceive('check')->andReturn(false);
        $this->app['sentinel'] = $sentinel;

        ob_start();
        $this->app->run();
        ob_end_clean();

        $controller = new ProfileController();
        $controller->setApp($this->app);
        $response = $controller->passwordProcessAction($req);

        $this->assertContains(
            'Redirecting to /login',
            $response->getContent(),
            'Non-authenticated user was allowed to POST data to change a password'
        );
    }

    /**
     * @test
     */
    public function processPasswordHandlesInvalidFormCorrectly()
    {
        // Create our Request object
        $req = m::mock(Request::class);
        $req->shouldReceive('getMethod')->andReturn('post');

        $this->app['sentinel'] = $this->createSentinelWithLoggedInUser();

        ob_start();
        $this->app->run();
        ob_end_clean();

        $controller = new ProfileController();
        $controller->setApp($this->app);
        $response = $controller->passwordProcessAction($req);

        $this->assertContains(
            '<!-- id: profile/change_password -->',
            $response->getContent(),
            'Process Password did not handle invalid form correctly'
        );
    }

    /**
     * @test
     */
    public function processPasswordCannotChangeAnotherUsersPassword()
    {
        // Create our Request object
        $req = m::mock(Request::class);
        $req->shouldReceive('getMethod')->andReturn('post');

        $this->app['sentinel'] = $this->createSentinelWithLoggedInUser();
        $this->app['form.factory'] = $this->createChangePasswordFormFactory($req, 2); // 2 is a user id

        ob_start();
        $this->app->run();
        ob_end_clean();

        $controller = new ProfileController();
        $controller->setApp($this->app);
        $response = $controller->passwordProcessAction($req);

        $this->assertContains(
            'Redirecting to /dashboard',
            $response->getContent(),
            "You were allowed to POST data to change someone else's password"
        );
    }

    /**
     * @test
     */
    public function itHandlesNotUpdatingAUsersPasswordCorrectly()
    {
        // Create our Request object
        $req = m::mock(Request::class);
        $req->shouldReceive('getMethod')->andReturn('post');

        // Create our Sentinel object
        $sentinel = $this->createSentinelWithLoggedInUser();
        $sentinel->shouldReceive('update')->andReturn(false);
        $this->app['sentinel'] = $sentinel;

        $this->app['form.factory'] = $this->createChangePasswordFormFactory($req, 1); // 1 is logged in user's ID

        ob_start();
        $this->app->run();
        ob_end_clean();

        $controller = new ProfileController();
        $controller->setApp($this->app);
        $response = $controller->passwordProcessAction($req);

        $this->assertContains(
            'Redirecting to /dashboard',
            $response->getContent(),
            'Password was not succesfully updated'
        );
    }

    /**
     * @test
     */
    public function itHandlesUpdatingAUsersPasswordCorrectly()
    {
        // Create our Request object
        $req = m::mock(Request::class);
        $req->shouldReceive('getMethod')->andReturn('post');

        // Create our Sentinel object
        $sentinel = $this->createSentinelWithLoggedInUser();
        $sentinel->shouldReceive('update')->andReturn(true);
        $this->app['sentinel'] = $sentinel;

        $this->app['form.factory'] = $this->createChangePasswordFormFactory($req, 1); // 1 is logged in user's ID

        ob_start();
        $this->app->run();
        ob_end_clean();

        $controller = new ProfileController();
        $controller->setApp($this->app);
        $response = $controller->passwordProcessAction($req);

        $this->assertContains(
            'Redirecting to /dashboard',
            $response->getContent(),
            'User did not succesfully change their password'
        );
    }
}
