<?php namespace OpenCFP\Provider;

use Silex\Application;
use Silex\ServiceProviderInterface;

class RouteServiceProvider  implements ServiceProviderInterface
{
    /**
     * {@inheritdoc}
     */
    public function register(Application $app)
    {
        $app->get('/', 'OpenCFP\Http\Controller\PagesController::showHomepage')->bind('homepage');
        $app->get('/package', 'OpenCFP\Http\Controller\PagesController::showSpeakerPackage')->bind('speaker_package');
        $app->get('/ideas', 'OpenCFP\Http\Controller\PagesController::showTalkIdeas')->bind('talk_ideas');

        $secureRoutes = [];

        // User Dashboard
        $secureRoutes[] = $app->get('/dashboard', 'OpenCFP\Http\Controller\DashboardController::showSpeakerProfile')->bind('dashboard');

        // Talks
        $secureRoutes[] = $app->get('/talk/edit/{id}', 'OpenCFP\Http\Controller\TalkController::editAction')->bind('talk_edit');
        $secureRoutes[] = $app->get('/talk/create', 'OpenCFP\Http\Controller\TalkController::createAction')->bind('talk_new');
        $secureRoutes[] = $app->post('/talk/create', 'OpenCFP\Http\Controller\TalkController::processCreateAction')->bind('talk_create');
        $secureRoutes[] = $app->post('/talk/update', 'OpenCFP\Http\Controller\TalkController::updateAction')->bind('talk_update');
        $secureRoutes[] = $app->post('/talk/delete', 'OpenCFP\Http\Controller\TalkController::deleteAction')->bind('talk_delete');
        $secureRoutes[] = $app->get('/talk/{id}', 'OpenCFP\Http\Controller\TalkController::viewAction')->bind('talk_view');

        // Login/Logout
        $secureRoutes[] = $app->get('/login', 'OpenCFP\Http\Controller\SecurityController::indexAction')->bind('login');
        $secureRoutes[] = $app->post('/login', 'OpenCFP\Http\Controller\SecurityController::processAction')->bind('login_check');
        $secureRoutes[] = $app->get('/logout', 'OpenCFP\Http\Controller\SecurityController::outAction')->bind('logout');

        // Create Account
        $secureRoutes[] = $app->get('/signup', 'OpenCFP\Http\Controller\SignupController::indexAction')->bind('user_new');
        $secureRoutes[] = $app->post('/signup', 'OpenCFP\Http\Controller\SignupController::processAction')->bind('user_create');
        $secureRoutes[] = $app->get('/signup/success', 'OpenCFP\Http\Controller\SignupController::successAction')->bind('user_success');

        // Edit Profile/Account
        $secureRoutes[] = $app->get('/profile/edit/{id}', 'OpenCFP\Http\Controller\ProfileController::editAction')->bind('user_edit');
        $secureRoutes[] = $app->post('/profile/edit', 'OpenCFP\Http\Controller\ProfileController::processAction')->bind('user_update');

        // Change/forgot Password
        $secureRoutes[] = $app->get('/profile/change_password', 'OpenCFP\Http\Controller\ProfileController::passwordAction')->bind('password_edit');
        $secureRoutes[] = $app->post('/profile/change_password', 'OpenCFP\Http\Controller\ProfileController::passwordProcessAction')->bind('password_change');
        $secureRoutes[] = $app->get('/forgot', 'OpenCFP\Http\Controller\ForgotController::indexAction')->bind('forgot_password');
        $secureRoutes[] = $app->post('/forgot', 'OpenCFP\Http\Controller\ForgotController::sendResetAction')->bind('forgot_password_create');
        $secureRoutes[] = $app->get('/forgot_success', 'OpenCFP\Http\Controller\ForgotController::successAction')->bind('forgot_password_success');
        $secureRoutes[] = $app->post('/reset', 'OpenCFP\Http\Controller\ForgotController::resetAction')->bind('reset_password_create');
        $secureRoutes[] = $app->get('/reset/{user_id}/{reset_code}', 'OpenCFP\Http\Controller\ForgotController::processResetAction')->bind('reset_password');
        $secureRoutes[] = $app->post('/updatepassword', 'OpenCFP\Http\Controller\ForgotController::updatePasswordAction')->bind('password_update');

        // Admin Routes
        $secureRoutes[] = $app->get('/admin', 'OpenCFP\Http\Controller\Admin\DashboardController::indexAction')->bind('admin');

        // Admin::Talks
        $secureRoutes[] = $app->get('/admin/talks', 'OpenCFP\Http\Controller\Admin\TalksController::indexAction')->bind('admin_talks');
        $secureRoutes[] = $app->get('/admin/talks/{id}', 'OpenCFP\Http\Controller\Admin\TalksController::viewAction')->bind('admin_talk_view');
        $secureRoutes[] = $app->post('/admin/talks/{id}/favorite', 'OpenCFP\Http\Controller\Admin\TalksController::favoriteAction')->bind('admin_talk_favorite');
        $secureRoutes[] = $app->post('/admin/talks/{id}/select', 'OpenCFP\Http\Controller\Admin\TalksController::selectAction')->bind('admin_talk_select');

        // Admin::Speakers
        $secureRoutes[] = $app->get('/admin/speakers', 'OpenCFP\Http\Controller\Admin\SpeakersController::indexAction')->bind('admin_speakers');
        $secureRoutes[] = $app->get('/admin/speakers/{id}', 'OpenCFP\Http\Controller\Admin\SpeakersController::viewAction')->bind('admin_speaker_view');
        $secureRoutes[] = $app->get('/admin/speakers/delete/{id}', 'OpenCFP\Http\Controller\Admin\SpeakersController::deleteAction')->bind('admin_speaker_delete');
        $secureRoutes[] = $app->get('/admin/admins', 'OpenCFP\Http\Controller\Admin\AdminsController::indexAction')->bind('admin_admins');
        $secureRoutes[] = $app->get('/admin/admins/{id}', 'OpenCFP\Http\Controller\Admin\AdminsController::removeAction')->bind('admin_admin_delete');

        // Admin::Review
        $secureRoutes[] = $app->get('/admin/review', 'OpenCFP\Http\Controller\Admin\ReviewController::indexAction')->bind('admin_reviews');

        // API end points
        $secureRoutes[] = $app->post('/api/talk/add', 'OpenCFP\Http\Controller\Api\TalksController::addAction')->bind('api_talk_add');
        $secureRoutes[] = $app->get('/api/talk/edit/{id}', 'OpenCFP\Http\Controller\Api\TalksController::editAction')->bind('api_talk_edit');
        $secureRoutes[] = $app->post('/api/talk/delete/{$id}', 'OpenCFP\Http\Controller\Api\TalksController::deleteAction')->bind('api_talk_delete');
        if ($app->config('application.secure_ssl')) {
            foreach ($secureRoutes as $route) {
                $route->requireHttps();
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function boot(Application $app)
    {
    }
}
