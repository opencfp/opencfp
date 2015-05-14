<?php namespace OpenCFP\Provider; 

use OpenCFP\Application\Speakers;
use OpenCFP\Http\Controller\NeoTalkController;
use OpenCFP\Infrastructure\Auth\SentryIdentityProvider;
use OpenCFP\Infrastructure\Persistence\SpotSpeakerRepository;
use OpenCFP\Infrastructure\Persistence\SpotTalkRepository;
use Silex\Application;
use Silex\ServiceProviderInterface;

class ApplicationServiceProvider implements ServiceProviderInterface
{

    /**
     * {@inheritdoc}
     */
    public function register(Application $app)
    {
        $this->bindApplicationServices($app);
        $this->bindControllersAsServices($app);
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
    protected function bindApplicationServices(Application $app)
    {
        $app['application.speakers'] = $app->share(
            function ($app) {
                $userMapper = $app['spot']->mapper('OpenCFP\Domain\Entity\User');
                $talkMapper = $app['spot']->mapper('OpenCFP\Domain\Entity\Talk');

                return new Speakers(
                    new SentryIdentityProvider($app['sentry']),
                    new SpotSpeakerRepository($userMapper),
                    new SpotTalkRepository($talkMapper)
                );
            }
        );
    }

    private function bindControllersAsServices($app)
    {
        
    }
}