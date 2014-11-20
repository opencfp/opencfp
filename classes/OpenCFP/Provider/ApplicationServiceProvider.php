<?php namespace OpenCFP\Provider; 

use OpenCFP\Application\Speakers;
use OpenCFP\Infrastructure\Persistence\SpotSpeakerRepository;
use Silex\Application;
use Silex\ServiceProviderInterface;

class ApplicationServiceProvider implements ServiceProviderInterface
{

    /**
     * {@inheritdoc}
     */
    public function register(Application $app)
    {
        $app['application.speakers'] = $app->share(function($app) {
            $mapper = $app['spot']->mapper('OpenCFP\Domain\Entity\User');

            return new Speakers(
                new SpotSpeakerRepository($mapper)
            );
        });
    }

    /**
     * {@inheritdoc}
     */
    public function boot(Application $app)
    {
    }
}