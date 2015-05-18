<?php namespace OpenCFP\Provider; 

use OpenCFP\Application\Speakers;
use OpenCFP\Domain\CallForProposal;
use OpenCFP\Http\API\TalkController;
use OpenCFP\Infrastructure\Auth\SentryIdentityProvider;
use OpenCFP\Infrastructure\Auth\UhhhmIdentityProvider;
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
                $speakerRepository = new SpotSpeakerRepository($userMapper);

                return new Speakers(
                    new CallForProposal(new \DateTime($app->config('application.enddate'))),
                    new SentryIdentityProvider($app['sentry'], $speakerRepository),
                    $speakerRepository,
                    new SpotTalkRepository($talkMapper),
                    $app['dispatcher']
                );
            }
        );

        $app['application.speakers.api'] = $app->share(
            function ($app) {
                $userMapper = $app['spot']->mapper('OpenCFP\Domain\Entity\User');
                $talkMapper = $app['spot']->mapper('OpenCFP\Domain\Entity\Talk');
                $speakerRepository = new SpotSpeakerRepository($userMapper);

                return new Speakers(
                    new CallForProposal(new \DateTime($app->config('application.enddate'))),
                    new UhhhmIdentityProvider($app['request'], $speakerRepository),
                    $speakerRepository,
                    new SpotTalkRepository($talkMapper),
                    $app['dispatcher']
                );
            }
        );
    }

    private function bindControllersAsServices($app)
    {
        $app['controller.api.talk'] = $app->share(function ($app) {
            return new TalkController($app['application.speakers.api']);
        });

        $app['controller.oauth'] = $app->share(function($app) {
            
        });
    }
}