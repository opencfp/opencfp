<?php

namespace OpenCFP\Provider\Gateways;

use OpenCFP\Domain\Services\Authentication;
use OpenCFP\Infrastructure\Auth\AdminAccess;
use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Silex\Api\BootableProviderInterface;
use Silex\Application;
use Silex\ControllerCollection;
use Symfony\Component\HttpFoundation\Request;
use Twig_Environment;
use Twig_SimpleFunction;

class WebGatewayProvider implements BootableProviderInterface, ServiceProviderInterface
{
    public function register(Container $app)
    {
    }

    public function boot(Application $app)
    {
        // @codingStandardsIgnoreStart
        /* @var $web ControllerCollection */
        $web = $app['controllers_factory'];

        $web->before(new RequestCleaner($app['purifier']));
        $app->before(function (Request $request, Container $app) {
            /* @var Twig_Environment $twig */
            $twig = $app['twig'];

            $twig->addGlobal('current_page', $request->getRequestUri());
            $twig->addGlobal('cfp_open', $app['callforproposal']->isOpen());

            $twig->addFunction(new Twig_SimpleFunction('active', function ($route) use ($app, $request) {
                return $app['url_generator']->generate($route) == $request->getRequestUri();
            }));

            // Authentication
            if ($app[Authentication::class]->check()) {
                $twig->addGlobal('user', $app[Authentication::class]->user());
                $twig->addGlobal('user_is_admin', $app[Authentication::class]->user()->hasAccess('admin'));
            }

            if ($app['session']->has('flash')) {
                $twig->addGlobal('flash', $app['session']->get('flash'));
                $app['session']->set('flash', null);
            }
        }, Application::EARLY_EVENT);

        if ($app->config('application.secure_ssl')) {
            $web->requireHttps();
        }
        $adminAccess = function ($request, $app) {
            return AdminAccess::userHasAccess($app);
        };

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
        $web->get('/admin', 'OpenCFP\Http\Controller\Admin\DashboardController::indexAction')->bind('admin')->before($adminAccess);

        // Admin::Talks
        $web->get('/admin/talks', 'OpenCFP\Http\Controller\Admin\TalksController::indexAction')->bind('admin_talks')->before($adminAccess);
        $web->get('/admin/talks/{id}', 'OpenCFP\Http\Controller\Admin\TalksController::viewAction')->bind('admin_talk_view')->before($adminAccess);
        $web->post('/admin/talks/{id}/favorite', 'OpenCFP\Http\Controller\Admin\TalksController::favoriteAction')->bind('admin_talk_favorite')->before($adminAccess);
        $web->post('/admin/talks/{id}/select', 'OpenCFP\Http\Controller\Admin\TalksController::selectAction')->bind('admin_talk_select')->before($adminAccess);
        $web->post('/admin/talks/{id}/comment', 'OpenCFP\Http\Controller\Admin\TalksController::commentCreateAction')->bind('admin_talk_comment_create')->before($adminAccess);
        $web->post('/admin/talks/{id}/rate', 'OpenCFP\Http\Controller\Admin\TalksController::rateAction')->bind('admin_talk_rate')->before($adminAccess);

        // Admin::Speakers
        $web->get('/admin/speakers', 'OpenCFP\Http\Controller\Admin\SpeakersController::indexAction')->bind('admin_speakers')->before($adminAccess);;
        $web->get('/admin/speakers/{id}', 'OpenCFP\Http\Controller\Admin\SpeakersController::viewAction')->bind('admin_speaker_view')->before($adminAccess);;
        $web->get('/admin/speakers/{id}/promote', 'OpenCFP\Http\Controller\Admin\SpeakersController::promoteAction')->bind('admin_speaker_promote')->before($adminAccess);;
        $web->get('/admin/speakers/{id}/demote', 'OpenCFP\Http\Controller\Admin\SpeakersController::demoteAction')->bind('admin_speaker_demote')->before($adminAccess);;
        $web->get('/admin/speakers/delete/{id}', 'OpenCFP\Http\Controller\Admin\SpeakersController::deleteAction')->bind('admin_speaker_delete')->before($adminAccess);;

        // CSV Exports
        $web->get('/admin/export/csv', 'OpenCFP\Http\Controller\Admin\ExportsController::attributedTalksExportAction')->bind('admin_export_csv')->before($adminAccess);
        $web->get('/admin/export/csv/anon', 'OpenCFP\Http\Controller\Admin\ExportsController::anonymousTalksExportAction')->bind('admin_export_csv_anon')->before($adminAccess);
        $web->get('/admin/export/csv/selected', 'OpenCFP\Http\Controller\Admin\ExportsController::selectedTalksExportAction')->bind('admin_export_csv_selected')->before($adminAccess);
        $web->get('/admin/export/csv/emails', 'OpenCFP\Http\Controller\Admin\ExportsController::emailExportAction')->bind('admin_export_csv_emails')->before($adminAccess);

        $app->mount('/', $web);
        // @codingStandardsIgnoreEnd
    }
}
