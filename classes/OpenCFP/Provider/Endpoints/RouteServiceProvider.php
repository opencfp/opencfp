<?php namespace OpenCFP\Provider\Endpoints;

use Silex\Application;
use Silex\ControllerCollection;
use Silex\ServiceProviderInterface;
use Symfony\Component\HttpFoundation\Request;

class RouteServiceProvider  implements ServiceProviderInterface
{

    /**
     * {@inheritdoc}
     */
    public function register(Application $app)
    {
        $this->mountWebRoutes($app);

        if ($app->config('api.enabled')) {
            $this->mountApiRoutes($app);
        }
    }

    public function cleanRequest(Request $request, Application $application)
    {
        $request->query->replace($this->clean($request->query->all(), $application['purifier']));
        $request->request->replace($this->clean($request->request->all(), $application['purifier']));
    }

    public function clean(array $data, \HTMLPurifier $purifier)
    {
        $sanitized = [];

        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $sanitized[$key] = $this->clean($value, $purifier);
            } else {
                $sanitized[$key] = $purifier->purify($value);;
            }
        }

        return $sanitized;
    }

    /**
     * {@inheritdoc}
     */
    public function boot(Application $app)
    {
    }

    /**
     * @param Application $app
     */
    private function mountWebRoutes(Application $app)
    {
        /* @var $web ControllerCollection */
        $web = $app['controllers_factory'];
        $web->before([$this, 'cleanRequest']);

        $app->before(function (Request $request, Application $app) {
            $app['twig']->addGlobal('current_page', $request->getRequestUri());
            $app['twig']->addGlobal('cfp_open', strtotime('now') < strtotime($app->config('application.enddate') . ' 11:59 PM'));

            if ($app['sentry']->check()) {
                $app['twig']->addGlobal('user', $app['sentry']->getUser());
                $app['twig']->addGlobal('user_is_admin', $app['sentry']->getUser()->hasAccess('admin'));
            }

            if ($app['session']->has('flash')) {
                $app['twig']->addGlobal('flash', $app['session']->get('flash'));
                $app['session']->set('flash', null);
            }
        });

        $web->get('/', 'OpenCFP\Http\Controller\PagesController::showHomepage')->bind('homepage');
        $web->get('/package', 'OpenCFP\Http\Controller\PagesController::showSpeakerPackage')->bind('speaker_package');
        $web->get('/ideas', 'OpenCFP\Http\Controller\PagesController::showTalkIdeas')->bind('talk_ideas');

        // User Dashboard
        $web->get('/dashboard', 'OpenCFP\Http\Controller\DashboardController::showSpeakerProfile')->bind('dashboard');

        // Talks
        $web->get('/talk/edit/{id}', 'OpenCFP\Http\Controller\TalkController::editAction')->bind('talk_edit');
        $web->get('/talk/create', 'OpenCFP\Http\Controller\TalkController::createAction')->bind('talk_new');
        $web->post('/talk/create', 'OpenCFP\Http\Controller\TalkController::processCreateAction')->bind('talk_create');
        $web->post('/talk/update', 'OpenCFP\Http\Controller\TalkController::updateAction')->bind('talk_update');
        $web->post('/talk/delete', 'OpenCFP\Http\Controller\TalkController::deleteAction')->bind('talk_delete');
        $web->get('/talk/{id}', 'OpenCFP\Http\Controller\TalkController::viewAction')->bind('talk_view');

        // Login/Logout
        $web->get('/login', 'OpenCFP\Http\Controller\SecurityController::indexAction')->bind('login');
        $web->post('/login', 'OpenCFP\Http\Controller\SecurityController::processAction')->bind('login_check');
        $web->get('/logout', 'OpenCFP\Http\Controller\SecurityController::outAction')->bind('logout');

        // Create Account
        $web->get('/signup', 'OpenCFP\Http\Controller\SignupController::indexAction')->bind('user_new');
        $web->post('/signup', 'OpenCFP\Http\Controller\SignupController::processAction')->bind('user_create');
        $web->get('/signup/success', 'OpenCFP\Http\Controller\SignupController::successAction')->bind('user_success');

        // Edit Profile/Account
        $web->get('/profile/edit/{id}', 'OpenCFP\Http\Controller\ProfileController::editAction')->bind('user_edit');
        $web->post('/profile/edit', 'OpenCFP\Http\Controller\ProfileController::processAction')->bind('user_update');

        // Change/forgot Password
        $web->get('/profile/change_password', 'OpenCFP\Http\Controller\ProfileController::passwordAction')->bind('password_edit');
        $web->post('/profile/change_password', 'OpenCFP\Http\Controller\ProfileController::passwordProcessAction')->bind('password_change');
        $web->get('/forgot', 'OpenCFP\Http\Controller\ForgotController::indexAction')->bind('forgot_password');
        $web->post('/forgot', 'OpenCFP\Http\Controller\ForgotController::sendResetAction')->bind('forgot_password_create');
        $web->get('/forgot_success', 'OpenCFP\Http\Controller\ForgotController::successAction')->bind('forgot_password_success');
        $web->post('/reset', 'OpenCFP\Http\Controller\ForgotController::resetAction')->bind('reset_password_create');
        $web->get('/reset/{user_id}/{reset_code}', 'OpenCFP\Http\Controller\ForgotController::processResetAction')->bind('reset_password');
        $web->post('/updatepassword', 'OpenCFP\Http\Controller\ForgotController::updatePasswordAction')->bind('password_update');

        // Admin Routes
        $web->get('/admin', 'OpenCFP\Http\Controller\Admin\DashboardController::indexAction')->bind('admin');

        // Admin::Talks
        $web->get('/admin/talks', 'OpenCFP\Http\Controller\Admin\TalksController::indexAction')->bind('admin_talks');
        $web->get('/admin/talks/{id}', 'OpenCFP\Http\Controller\Admin\TalksController::viewAction')->bind('admin_talk_view');
        $web->post('/admin/talks/{id}/favorite', 'OpenCFP\Http\Controller\Admin\TalksController::favoriteAction')->bind('admin_talk_favorite');
        $web->post('/admin/talks/{id}/select', 'OpenCFP\Http\Controller\Admin\TalksController::selectAction')->bind('admin_talk_select');

        // Admin::Speakers
        $web->get('/admin/speakers', 'OpenCFP\Http\Controller\Admin\SpeakersController::indexAction')->bind('admin_speakers');
        $web->get('/admin/speakers/{id}', 'OpenCFP\Http\Controller\Admin\SpeakersController::viewAction')->bind('admin_speaker_view');
        $web->get('/admin/speakers/delete/{id}', 'OpenCFP\Http\Controller\Admin\SpeakersController::deleteAction')->bind('admin_speaker_delete');
        $web->get('/admin/admins', 'OpenCFP\Http\Controller\Admin\AdminsController::indexAction')->bind('admin_admins');
        $web->get('/admin/admins/{id}', 'OpenCFP\Http\Controller\Admin\AdminsController::removeAction')->bind('admin_admin_delete');

        // Admin::Review
        $web->get('/admin/review', 'OpenCFP\Http\Controller\Admin\ReviewController::indexAction')->bind('admin_reviews');

        if ($app->config('application.secure_ssl')) {
            $web->requireHttps();
        }

        $app->mount('/', $web);
    }

    private function mountApiRoutes($app)
    {
        /* @var $api ControllerCollection */
        $api = $app['controllers_factory'];
        $api->before([$this, 'cleanRequest']);
        $api->before(function (Request $request) {
            $request->headers->set('Accept', 'application/json');

            if (0 === strpos($request->headers->get('Content-Type'), 'application/json')) {
                $data = json_decode($request->getContent(), true);
                $request->request->replace(is_array($data) ? $data : array());
            }
        });

        if ($app->config('application.secure_ssl')) {
            $api->requireHttps();
        }

        $api->get('/me', 'controller.api.profile:handleShowSpeakerProfile');
        $api->get('/talks', 'controller.api.talk:handleViewAllTalks');
        $api->post('/talks', 'controller.api.talk:handleSubmitTalk');
        $api->get('/talks/{id}', 'controller.api.talk:handleViewTalk');

        $app->mount('/api', $api);
    }
}
