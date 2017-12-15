<?php

declare(strict_types=1);

/**
 * Copyright (c) 2013-2017 OpenCFP
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @see https://github.com/opencfp/opencfp
 */

namespace OpenCFP\Provider\Gateways;

use Illuminate\Database\Capsule\Manager as Capsule;
use OpenCFP\Domain\CallForPapers;
use OpenCFP\Domain\Services\AccountManagement;
use OpenCFP\Domain\Services\AirportInformationDatabase;
use OpenCFP\Domain\Services\Authentication;
use OpenCFP\Domain\Talk\TalkFilter;
use OpenCFP\Domain\Talk\TalkHandler;
use OpenCFP\Http\Action;
use OpenCFP\Http\Controller\Admin;
use OpenCFP\Http\Controller\ForgotController;
use OpenCFP\Http\Controller\PagesController;
use OpenCFP\Http\Controller\ProfileController;
use OpenCFP\Http\Controller\Reviewer;
use OpenCFP\Http\Controller\SecurityController;
use OpenCFP\Http\Controller\SignupController;
use OpenCFP\Http\Controller\TalkController;
use OpenCFP\Http\View\TalkHelper;
use OpenCFP\Infrastructure\Auth\CsrfValidator;
use OpenCFP\Infrastructure\Event\AuthenticationListener;
use OpenCFP\Infrastructure\Event\CsrfValidationListener;
use OpenCFP\Infrastructure\Event\RequestCleanerListener;
use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Silex\Api\BootableProviderInterface;
use Silex\Api\EventListenerProviderInterface;
use Silex\Application;
use Silex\ControllerCollection;
use Swift_Mailer;
use Swift_SmtpTransport;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

final class WebGatewayProvider implements
    BootableProviderInterface,
    ServiceProviderInterface,
    EventListenerProviderInterface
{
    public function register(Container $app)
    {
        $app[Action\DashboardAction::class] = function ($app) {
            return new Action\DashboardAction(
                $app['application.speakers'],
                $app['twig'],
                $app['url_generator']
            );
        };

        $app[ForgotController::class] = function ($app) {
            return new ForgotController(
                $app['form.factory'],
                $app[AccountManagement::class],
                $app['reset_emailer'],
                $app['twig'],
                $app['url_generator']
            );
        };

        $app[PagesController::class] = function ($app) {
            return new PagesController($app['twig'], $app['url_generator']);
        };

        $app[ProfileController::class] = function ($app) {
            return new ProfileController(
                $app[Authentication::class],
                $app['purifier'],
                $app['profile_image_processor'],
                $app['twig'],
                $app['url_generator'],
                $app['path']
            );
        };

        $app[SecurityController::class] = function ($app) {
            return new SecurityController(
                $app[Authentication::class],
                $app['twig'],
                $app['url_generator']
            );
        };

        $app[SignupController::class] = function ($app) {
            return new SignupController(
                $app[Authentication::class],
                $app[AccountManagement::class],
                $app[CallForPapers::class],
                $app['twig'],
                $app['url_generator']
            );
        };

        $app[TalkController::class] = function ($app) {
            return new TalkController(
                $app[Authentication::class],
                $app['application.speakers'],
                $app[TalkHelper::class],
                $app[CallForPapers::class],
                $app['purifier'],
                $app['mailer'],
                $app['twig'],
                $app['url_generator'],
                $app->config('application.email'),
                $app->config('application.title'),
                $app->config('application.enddate')
            );
        };

        // Admin controllers
        $app[Admin\DashboardController::class] = function ($app) {
            return new Admin\DashboardController(
                $app[Authentication::class],
                $app['twig'],
                $app['url_generator']
            );
        };

        $app[Admin\ExportsController::class] = function ($app) {
            return new Admin\ExportsController($app['twig'], $app['url_generator'], $app['session']);
        };

        $app[Admin\SpeakersController::class] = function ($app) {
            return new Admin\SpeakersController(
                $app[Authentication::class],
                $app[AccountManagement::class],
                $app[AirportInformationDatabase::class],
                $app[Capsule::class],
                $app['twig'],
                $app['url_generator'],
                $app->config('application.airport'),
                $app->config('application.arrival'),
                $app->config('application.departure'),
                $app['path']
            );
        };

        $app[Admin\TalksController::class] = function ($app) {
            return new Admin\TalksController(
                $app[Authentication::class],
                $app[TalkFilter::class],
                $app[TalkHandler::class],
                $app['twig'],
                $app['url_generator']
            );
        };

        // Reviewer controllers
        $app[Reviewer\DashboardController::class] = function ($app) {
            return new Reviewer\DashboardController(
                $app[Authentication::class],
                $app['twig'],
                $app['url_generator']
            );
        };

        $app[Reviewer\SpeakersController::class] = function ($app) {
            return new Reviewer\SpeakersController(
                $app['twig'],
                $app['url_generator'],
                $app->config('reviewer.users') ?: [],
                $app['path']
            );
        };

        $app[Reviewer\TalksController::class] = function ($app) {
            return new Reviewer\TalksController(
                $app[Authentication::class],
                $app[TalkFilter::class],
                $app[TalkHandler::class],
                $app['twig'],
                $app['url_generator']
            );
        };
    }

    public function boot(Application $app)
    {
        // @codingStandardsIgnoreStart
        /* @var $web ControllerCollection */
        $web = $app['controllers_factory'];

        if ($app->config('application.secure_ssl')) {
            $app->requireHttps();
        }

        $web->get('/', 'OpenCFP\Http\Controller\PagesController::homepageAction')
            ->bind('homepage');
        $web->get('/package', 'OpenCFP\Http\Controller\PagesController::speakerPackageAction')
            ->bind('speaker_package');
        $web->get('/ideas', 'OpenCFP\Http\Controller\PagesController::talkIdeasAction')
            ->bind('talk_ideas');

        // User Dashboard
        $web->get('/dashboard', Action\DashboardAction::class)->bind('dashboard');

        // Talks
        $web->get('/talk/edit/{id}', 'OpenCFP\Http\Controller\TalkController::editAction')
            ->bind('talk_edit')->value('_require_csrf_token', true);
        $web->get('/talk/create', 'OpenCFP\Http\Controller\TalkController::createAction')
            ->bind('talk_new');
        $web->post('/talk/create', 'OpenCFP\Http\Controller\TalkController::processCreateAction')
            ->bind('talk_create')->value('_require_csrf_token', true);
        $web->post('/talk/update', 'OpenCFP\Http\Controller\TalkController::updateAction')
            ->bind('talk_update')->value('_require_csrf_token', true);
        $web->post('/talk/delete', 'OpenCFP\Http\Controller\TalkController::deleteAction')
            ->bind('talk_delete')->value('_require_csrf_token', true);
        $web->get('/talk/{id}', 'OpenCFP\Http\Controller\TalkController::viewAction')
            ->bind('talk_view');

        // Login/Logout
        $web->get('/login', 'OpenCFP\Http\Controller\SecurityController::indexAction')
            ->bind('login');
        $web->post('/login', 'OpenCFP\Http\Controller\SecurityController::processAction')
            ->bind('login_check');
        $web->get('/logout', 'OpenCFP\Http\Controller\SecurityController::outAction')
            ->bind('logout');

        // Create Account
        $web->get('/signup', 'OpenCFP\Http\Controller\SignupController::indexAction')
            ->bind('user_new');
        $web->post('/signup', 'OpenCFP\Http\Controller\SignupController::processAction')
            ->bind('user_create');

        // Edit Profile/Account
        $web->get('/profile/edit/{id}', 'OpenCFP\Http\Controller\ProfileController::editAction')
            ->bind('user_edit');
        $web->post('/profile/edit', 'OpenCFP\Http\Controller\ProfileController::processAction')
            ->bind('user_update');

        // Change/forgot Password
        $web->get('/profile/change_password', 'OpenCFP\Http\Controller\ProfileController::passwordAction')
            ->bind('password_edit');
        $web->post('/profile/change_password', 'OpenCFP\Http\Controller\ProfileController::passwordProcessAction')
            ->bind('password_change');
        $web->get('/forgot', 'OpenCFP\Http\Controller\ForgotController::indexAction')
            ->bind('forgot_password');
        $web->post('/forgot', 'OpenCFP\Http\Controller\ForgotController::sendResetAction')
            ->bind('forgot_password_create');
        $web->post('/reset', 'OpenCFP\Http\Controller\ForgotController::resetAction')
            ->bind('reset_password_create');
        $web->get('/reset/{user_id}/{reset_code}', 'OpenCFP\Http\Controller\ForgotController::processResetAction')
            ->bind('reset_password');
        $web->post('/updatepassword', 'OpenCFP\Http\Controller\ForgotController::updatePasswordAction')
            ->bind('password_update');

        /** @var ControllerCollection $admin */
        $admin = $app['controllers_factory'];

        // Admin Routes
        $admin->get('/', 'OpenCFP\Http\Controller\Admin\DashboardController::indexAction')
            ->bind('admin');

        // Admin::Talks
        $admin->get('/talks', 'OpenCFP\Http\Controller\Admin\TalksController::indexAction')
            ->bind('admin_talks');
        $admin->get('/talks/{id}', 'OpenCFP\Http\Controller\Admin\TalksController::viewAction')
            ->bind('admin_talk_view');
        $admin->post('/talks/{id}/favorite', 'OpenCFP\Http\Controller\Admin\TalksController::favoriteAction')
            ->bind('admin_talk_favorite');
        $admin->post('/talks/{id}/select', 'OpenCFP\Http\Controller\Admin\TalksController::selectAction')
            ->bind('admin_talk_select');
        $admin->post('/talks/{id}/comment', 'OpenCFP\Http\Controller\Admin\TalksController::commentCreateAction')
            ->bind('admin_talk_comment_create');
        $admin->post('/talks/{id}/rate', 'OpenCFP\Http\Controller\Admin\TalksController::rateAction')
            ->bind('admin_talk_rate');

        // Admin::Speakers
        $admin->get('/speakers', 'OpenCFP\Http\Controller\Admin\SpeakersController::indexAction')
            ->bind('admin_speakers');
        $admin->get('/speakers/{id}', 'OpenCFP\Http\Controller\Admin\SpeakersController::viewAction')
            ->bind('admin_speaker_view');
        $admin->get('/speakers/{id}/promote', 'OpenCFP\Http\Controller\Admin\SpeakersController::promoteAction')
            ->bind('admin_speaker_promote')->value('_require_csrf_token', true);
        $admin->get('/speakers/{id}/demote', 'OpenCFP\Http\Controller\Admin\SpeakersController::demoteAction')
            ->bind('admin_speaker_demote')->value('_require_csrf_token', true);
        $admin->get('/speakers/delete/{id}', 'OpenCFP\Http\Controller\Admin\SpeakersController::deleteAction')
            ->bind('admin_speaker_delete')->value('_require_csrf_token', true);

        // CSV Exports
        $admin->get('/export/csv', 'OpenCFP\Http\Controller\Admin\ExportsController::attributedTalksExportAction')
            ->bind('admin_export_csv');
        $admin->get('/export/csv/anon', 'OpenCFP\Http\Controller\Admin\ExportsController::anonymousTalksExportAction')
            ->bind('admin_export_csv_anon');
        $admin->get('/export/csv/selected', 'OpenCFP\Http\Controller\Admin\ExportsController::selectedTalksExportAction')
            ->bind('admin_export_csv_selected');
        $admin->get('/export/csv/emails', 'OpenCFP\Http\Controller\Admin\ExportsController::emailExportAction')
            ->bind('admin_export_csv_emails');
        $app->mount('/admin/', $admin);

        /** @var ControllerCollection $reviewer */
        $reviewer = $app['controllers_factory'];

        //Reviewer Routes
        $reviewer->get('/', 'OpenCFP\Http\Controller\Reviewer\DashboardController::indexAction')
            ->bind('reviewer');

        // Reviewer::Talks
        $reviewer->get('/talks', 'OpenCFP\Http\Controller\Reviewer\TalksController::indexAction')
            ->bind('reviewer_talks');
        $reviewer->get('/talks/{id}', 'OpenCFP\Http\Controller\Reviewer\TalksController::viewAction')
            ->bind('reviewer_talk_view');
        $reviewer->post('/talks/{id}/rate', 'OpenCFP\Http\Controller\Reviewer\TalksController::rateAction')
            ->bind('reviewer_talk_rate');

        // Reviewer::Speakers
        $reviewer->get('/speakers', 'OpenCFP\Http\Controller\Reviewer\SpeakersController::indexAction')
             ->bind('reviewer_speakers');
        $reviewer->get('/speakers/{id}', 'OpenCFP\Http\Controller\Reviewer\SpeakersController::viewAction')
            ->bind('reviewer_speaker_view');

        $app->mount('/reviewer/', $reviewer);

        $app->mount('/', $web);
        // @codingStandardsIgnoreEnd
    }

    public function subscribe(Container $app, EventDispatcherInterface $dispatcher)
    {
        $dispatcher->addSubscriber(new AuthenticationListener(
            $app[Authentication::class],
            $app['url_generator']
        ));
        $dispatcher->addSubscriber(new CsrfValidationListener(
            $app[CsrfValidator::class],
            $app['url_generator']
        ));
        $dispatcher->addSubscriber(new RequestCleanerListener($app['purifier']));
    }
}
