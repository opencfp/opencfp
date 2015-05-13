<?php

namespace OpenCFP\Http\Controller;

use Exception;
use OpenCFP\Application;
use OpenCFP\Application\Speakers;
use OpenCFP\Domain\Services\NotAuthenticatedException;
use OpenCFP\Domain\Talk\TalkSubmission;
use Symfony\Component\HttpFoundation\Request;

class NeoTalkController extends BaseController
{
    use FlashableTrait;

    /**
     * @var Speakers
     */
    private $speakers;

    public function __construct(Application $app, Speakers $speakers){
        parent::__construct($app);

        $this->speakers = $speakers;
    }

    /**
     * Check to see if the CfP for this app is still open
     *
     * @param  integer $current_time
     * @return boolean
     */
    public function isCfpOpen($current_time)
    {
        if ($current_time < strtotime($this->app->config('application.enddate') . ' 11:59 PM')) {
            return true;
        }

        return false;
    }

    public function handleSubmitTalk(Request $request)
    {
        // You can only create talks while the CfP is open
        // @todo this should be removed and the service layer should be honoring this. respond with an exception
        if ( ! $this->isCfpOpen(strtotime('now'))) {
            $this->app['session']->set('flash', [
                'type' => 'error',
                'short' => 'Error',
                'ext' => 'You cannot create talks once the call for papers has ended']
            );

            return $this->redirectTo('dashboard');
        }

        try {
            $submission = TalkSubmission::fromNative($request->request->all());
            $this->speakers->submitTalk($submission);
        } catch (NotAuthenticatedException $e) {
            return $this->redirectTo('login');
        } catch (Exception $e) {
            $this->app['session']->set('flash', [
                'type' => 'error',
                'short' => 'Error',
                'ext' => $e->getMessage()
            ]);

            return $this->redirectTo('dashboard');
        }

        return $this->redirectTo('dashboard');
    }
} 