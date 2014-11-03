<?php
namespace OpenCFP;

use Illuminate\Database\Capsule\Manager as Capsule;
use Swift_Mailer;
use Swift_SmtpTransport;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use OpenCFP\Config\ConfigINIFileLoader;
use OpenCFP\ProfileImageProcessor;
use Pimple;
use Twig_Environment;
use Twig_Loader_Filesystem;
use Aptoma\Twig\Extension\MarkdownExtension;
use Ciconia\Ciconia;
use Ciconia\Extension\Gfm as CiconiaExtension;

$environment = isset($_SERVER['CFP_ENV']) ? $_SERVER['CFP_ENV'] : 'development';
// $environment = isset($_SERVER['CFP_ENV']) ? $_SERVER['CFP_ENV'] : 'production';
// Set constant for app wide use
define('APP_ENV', $environment);
define('APP_DIR', dirname(dirname(__DIR__)));

class Bootstrap
{
    private $_app;
    private $_config;
    private $_twig;
    private $_purifier;

    function __construct(array $config = null)
    {
        $this->_config = $config;
        $this->initializeAutoLoader();
        $this->_app = $this->getApp();
    }

    function getApp()
    {
        if (isset($this->_app)) {
            return $this->_app;
        }

        // Initialize out Silex app and let's do it
        $app = new \Silex\Application();

        $app['config'] = $this->getConfigContainer();

        if ($this->getConfig('twig.debug')) {
            $app['debug'] = $this->getConfig('twig.debug');
        }

        // Register our session provider
        $app->register(new \Silex\Provider\SessionServiceProvider());
        $app->before(function ($request) use ($app) {
            $app['session']->start();
        });

        $app['url'] = $this->getConfig('application.url') . $this->getPort();
        $app['uploadPath'] = $this->getConfig('upload.path');
        $app['confAirport'] = $this->getConfig('application.airport');
        $app['arrival'] = $this->getConfig('application.arrival');
        $app['departure'] = $this->getConfig('application.departure');

        // Register the Twig provider and lazy-load the global values
        $app->register(
            new \Silex\Provider\TwigServiceProvider(),
            array(
                'twig.path' => APP_DIR . $this->getConfig('twig.template_dir'),
                'twig.options' => array(
                    'cache' => $this->getConfig('cache.enabled') ? $this->getTwigCacheDirectory() : false
                )
            )
        );
        $that = $this;
        $app['twig'] = $app->share($app->extend('twig', function ($twig, $app) use ($that) {
            // Twig Markdown Extension
            $markdown = new Ciconia();
            $markdown->addExtension(new CiconiaExtension\InlineStyleExtension());
            $markdown->addExtension(new CiconiaExtension\WhiteSpaceExtension());

            $engine = new \OpenCFP\Markdown\CiconiaEngine($markdown);
            $twig->addExtension(new MarkdownExtension($engine));

            $twig->addGlobal('site', array(
                'url' => $app['url'],
                'title' => $that->getConfig('application.title'),
                'email' => $that->getConfig('application.email'),
                'eventurl' => $that->getConfig('application.eventurl'),
                'enddate' => $that->getConfig('application.enddate')
            ));

            return $twig;
        }));

        // Register our use of the Form Service Provider
        $app->register(new \Silex\Provider\FormServiceProvider());
        $app->register(new \Silex\Provider\ValidatorServiceProvider());
        $app->register(new \Silex\Provider\UrlGeneratorServiceProvider());
        $app->register(new \Silex\Provider\TranslationServiceProvider(), array(
            'translator.messages' => array()
        ));

        $app['db'] = $this->getDb();
        $app['spot'] = $this->getSpot();
        $app['purifier'] = $this->getPurifier();
        $app['mailer'] = $this->getSwiftMailer();

        // We're using Sentry, so make it available to app
        $app['sentry'] = $app->share(function() use ($app) {
            $hasher = new \Cartalyst\Sentry\Hashing\NativeHasher;
            $userProvider = new \Cartalyst\Sentry\Users\Eloquent\Provider($hasher);
            $groupProvider = new \Cartalyst\Sentry\Groups\Eloquent\Provider;
            $throttleProvider = new \Cartalyst\Sentry\Throttling\Eloquent\Provider($userProvider);
            $session = new \OpenCFP\SymfonySentrySession($app['session']);
            $cookie = new \Cartalyst\Sentry\Cookies\NativeCookie(array());

            $sentry = new \Cartalyst\Sentry\Sentry(
                $userProvider,
                $groupProvider,
                $throttleProvider,
                $session,
                $cookie
            );

            \Cartalyst\Sentry\Facades\Native\Sentry::setupDatabaseResolver($app['db']);
            $throttleProvider->disable();
            return $sentry;
        });

        $app['twig'] = $app->share($app->extend('twig', function($twig, $app) {
            if ($app['sentry']->check()) {
                $twig->addGlobal('user', $app['sentry']->getUser());
            }

            return $twig;
        }));

        // Configure our flash messages functionality
        $app->before(function() use ($app) {
            $flash = $app['session']->get('flash');
            $app['session']->set('flash', null);

            if (!empty($flash)) {
                $app['twig']->addGlobal('flash', $flash);
            }
        });

        // Add current page global
        $app->before(function (Request $request) use ($app) {
            $app['twig']->addGlobal('current_page', $request->getRequestUri());
        });

        // Define error template paths
        if (!$app['debug']) {
            $app->error(function (\Exception $e, $code) use ($app) {
                switch ($code) {
                    case 401:
                        $message = $app['twig']->render('error/401.twig');
                        break;
                    case 403:
                        $message = $app['twig']->render('error/403.twig');
                        break;
                    case 404:
                        $message = $app['twig']->render('error/404.twig');
                        break;
                    default:
                        $message = $app['twig']->render('error/500.twig');
                }
                return new Response($message, $code);
            });
        }

        $app = $this->defineRoutes($app);

        // Add the starting date for submissions
        $app['cfpdate'] = $this->getConfig('application.cfpdate');

        // Profile image processor
        $app['profile_image_processor'] = $app->share(function () use ($app) {
            return new ProfileImageProcessor(APP_DIR . '/web/' . $app['uploadPath']);
        });

        return $app;
    }

    protected function defineRoutes($app)
    {
        // Load Static Routes
        $app->get('/', function() use($app) {
            $view = array();
            if ($app['sentry']->check()) {
                $user = $app['sentry']->getUser();
                $view['user'] = $user;
                $view['permissions']['admin'] = $user->hasPermission('admin');
            }

            $template = $app['twig']->loadTemplate('home.twig');
            return $template->render($view);
        });

        // Secondary Pages
        $app->get('/package', 'OpenCFP\Controller\DashboardController::packageAction')
            ->bind('speaker_package');
        $app->get('/ideas', 'OpenCFP\Controller\DashboardController::ideasAction')
            ->bind('talk_ideas');

        $secureRoutes = [];
        // User Dashboard
        $secureRoutes[] = $app->get('/dashboard', 'OpenCFP\Controller\DashboardController::indexAction')
            ->bind('dashboard');

        // Talks
        $secureRoutes[] = $app->get('/talk/edit/{id}', 'OpenCFP\Controller\TalkController::editAction')
            ->bind('talk_edit');
        $secureRoutes[] = $app->get('/talk/create', 'OpenCFP\Controller\TalkController::createAction')
            ->bind('talk_new')
            ->requireHttps();
        $secureRoutes[] = $app->post('/talk/create', 'OpenCFP\Controller\TalkController::processCreateAction')
            ->bind('talk_create');
        $secureRoutes[] = $app->post('/talk/update', 'OpenCFP\Controller\TalkController::updateAction')
            ->bind('talk_update');
        $secureRoutes[] = $app->post('/talk/delete', 'OpenCFP\Controller\TalkController::deleteAction')
            ->bind('talk_delete');
        $secureRoutes[] = $app->get('/talk/{id}', 'OpenCFP\Controller\TalkController::viewAction')
            ->bind('talk_view');

        // Login/Logout
        $secureRoutes[] = $app->get('/login', 'OpenCFP\Controller\SecurityController::indexAction')
            ->bind('login');
        $secureRoutes[] = $app->post('/login', 'OpenCFP\Controller\SecurityController::processAction')
            ->bind('login_check');
        $secureRoutes[] = $app->get('/logout', 'OpenCFP\Controller\SecurityController::outAction')
            ->bind('logout');

        // Create Account
        $secureRoutes[] = $app->get('/signup', 'OpenCFP\Controller\SignupController::indexAction')
            ->bind('user_new');
        $secureRoutes[] = $app->post('/signup', 'OpenCFP\Controller\SignupController::processAction')
            ->bind('user_create');
        $secureRoutes[] = $app->get('/signup/success', 'OpenCFP\Controller\SignupController::successAction')
            ->bind('user_success');

        // Edit Profile/Account
        $secureRoutes[] = $app->get('/profile/edit/{id}', 'OpenCFP\Controller\ProfileController::editAction')
            ->bind('user_edit');
        $secureRoutes[] = $app->post('/profile/edit', 'OpenCFP\Controller\ProfileController::processAction')
            ->bind('user_update');

        // Change/forgot Password
        $secureRoutes[] = $app->get('/profile/change_password', 'OpenCFP\Controller\ProfileController::passwordAction')
            ->bind('password_edit');
        $secureRoutes[] = $app->post('/profile/change_password', 'OpenCFP\Controller\ProfileController::passwordProcessAction')
            ->bind('password_update');
        $secureRoutes[] = $app->get('/forgot', 'OpenCFP\Controller\ForgotController::indexAction')
            ->bind('forgot_password');
        $secureRoutes[] = $app->post('/forgot', 'OpenCFP\Controller\ForgotController::sendResetAction')
            ->bind('forgot_password_create');
        $secureRoutes[] = $app->get('/forgot_success', 'OpenCFP\Controller\ForgotController::successAction')
            ->bind('forgot_password_success');
        $secureRoutes[] = $app->post('/reset', 'OpenCFP\Controller\ForgotController::resetAction')
            ->bind('reset_password_create');
        $secureRoutes[] = $app->get('/reset/{user_id}/{reset_code}', 'OpenCFP\Controller\ForgotController::processResetAction')
            ->bind('reset_password');
        $secureRoutes[] = $app->post('/updatepassword', 'OpenCFP\Controller\ForgotController::updatePasswordAction')
            ->bind('password_update');

        // Admin Routes
        $secureRoutes[] = $app->get('/admin', 'OpenCFP\Controller\Admin\DashboardController::indexAction')
            ->bind('admin');

        // Admin::Talks
        $secureRoutes[] = $app->get('/admin/talks', 'OpenCFP\Controller\Admin\TalksController::indexAction')
            ->bind('admin_talks');
        $secureRoutes[] = $app->get('/admin/talks/{id}', 'OpenCFP\Controller\Admin\TalksController::viewAction')
            ->bind('admin_talk_view');
        $secureRoutes[] = $app->post('/admin/talks/{id}/favorite', 'OpenCFP\Controller\Admin\TalksController::favoriteAction')
            ->bind('admin_talk_favorite');
        $secureRoutes[] = $app->post('/admin/talks/{id}/select', 'OpenCFP\Controller\Admin\TalksController::selectAction')
            ->bind('admin_talk_select');

        // Admin::Speakers
        $secureRoutes[] = $app->get('/admin/speakers', 'OpenCFP\Controller\Admin\SpeakersController::indexAction')
            ->bind('admin_speakers');
        $secureRoutes[] = $app->get('/admin/speakers/{id}', 'OpenCFP\Controller\Admin\SpeakersController::viewAction')
            ->bind('admin_speaker_view');
        $secureRoutes[] = $app->get('/admin/speakers/delete/{id}', 'OpenCFP\Controller\Admin\SpeakersController::deleteAction')
            ->bind('admin_speaker_delete');
        $secureRoutes[] = $app->get('/admin/admins', 'OpenCFP\Controller\Admin\AdminsController::indexAction')
            ->bind('admin_admins');
        $secureRoutes[] = $app->get('/admin/admins/{id}', 'OpenCFP\Controller\Admin\AdminsController::removeAction')
            ->bind('admin_admin_delete');

        // Admin::Review
        $secureRoutes[] = $app->get('/admin/review', 'OpenCFP\Controller\Admin\ReviewController::indexAction')
            ->bind('admin_reviews');

        if ($this->getConfig('application.secure_ssl')) {
            foreach ($secureRoutes as $route) {
                $route->requireHttps();
            }
        }

        return $app;
    }

    /**
     * @param string $configKey
     * @return mixed
     */
    public function getConfig($configKey)
    {
        $config = $this->getConfigContainer();

        if (!isset($config[$configKey])) {
            return null;
        }

        return $this->_config[$configKey];
    }

    public function getConfigContainer()
    {
        if (isset($this->_config)) {
            return $this->_config;
        }

        $loader = new ConfigINIFileLoader($this->getConfigPath());
        $configData = $loader->load();

        // Place our info into Pimple
        $this->_config = new \Pimple();

        foreach ($configData as $category => $info) {
            foreach ($info as $key => $value) {
                $this->_config["{$category}.{$key}"] = $value;
            }
        }

        return $this->_config;
    }

    public function getConfigPath()
    {
        return APP_DIR . "/config/config." . APP_ENV . ".ini";
    }

    private function getTwigCacheDirectory()
    {
        return APP_DIR . $this->getCacheDirectory() . '/twig';
    }

    private function getPurifierCacheDirectory()
    {
        return APP_DIR . $this->getCacheDirectory() . '/htmlpurifier';
    }

    private function getCacheDirectory()
    {
        return $this->getConfig('cache.directory') ?: '/cache';
    }

    public function getTwig()
    {
        if (!isset($this->_twig)) {
            // Initialize Twig
            $loader = new Twig_Loader_Filesystem(APP_DIR . $this->getConfig('twig.template_dir'));
            $this->_twig = new Twig_Environment($loader);
            $this->_twig->addGlobal('site', array(
                'url' => $this->getConfig('application.url'),
                'title' => $this->getConfig('application.title'),
                'email' => $this->getConfig('application.email'),
                'eventurl' => $this->getConfig('application.eventurl'),
                'enddate' => $this->getConfig('application.enddate')
            ));
        }
        return $this->_twig;
    }

    public function getPurifier() {
        if (!isset($this->_purifier)) {
            $config = \HTMLPurifier_Config::createDefault();

            if ($this->getConfig('cache.enabled')) {
                if (!is_dir($this->getPurifierCacheDirectory())) {
                    mkdir($this->getPurifierCacheDirectory(), 0755, true);
                }
                $config->set('Cache.SerializerPath', $this->getPurifierCacheDirectory());
            }

            $this->_purifier = new \HTMLPurifier($config);
        }

        return $this->_purifier;
    }

    /**
     * @return \PDO
     */
    public function getDb()
    {
        $container = $this->getConfigContainer();
        return new \PDO(
            $container['database.dsn'],
            $container['database.user'],
            $container['database.password']
        );
    }

    public function getSpot()
    {
        $cfg = new \Spot\Config();
        $cfg->addConnection('mysql', [
            'dbname' => $this->getConfig('database.database'),
            'user' => $this->getConfig('database.user'),
            'password' => $this->getConfig('database.password'),
            'host' => $this->getConfig('database.host'),
            'driver' => 'pdo_mysql'
        ]);
        return new \Spot\Locator($cfg);
    }

    private function initializeAutoLoader()
    {
        if (!file_exists(APP_DIR . '/vendor/autoload.php')) {
            throw new \Exception('Autoload file does not exist.  Did you run composer install?');
        }

        require APP_DIR . '/vendor/autoload.php';
    }

    private function getSwiftMailer()
    {
        // Create our Mailer object
        $transport = new Swift_SmtpTransport(
            $this->getConfig('smtp.host'),
            $this->getConfig('smtp.port')
        );

        if ($this->getConfig('smtp.user') !== '') {
            $transport->setUsername($this->getConfig('smtp.user'))
            ->setPassword($this->getConfig('smtp.password'));
        }

        if ($this->getConfig('smtp.encryption') !== '') {
            $transport->setEncryption($this->getConfig('smtp.encryption'));
        }

        return new Swift_Mailer($transport);
    }

    /**
     * @return string
     */
    private function getPort()
    {
        if (isset($_SERVER['SERVER_PORT']) && !in_array($_SERVER['SERVER_PORT'], array(80, 443))) {
            return ':' . $_SERVER['SERVER_PORT'];
        }

        return '';
    }

}
