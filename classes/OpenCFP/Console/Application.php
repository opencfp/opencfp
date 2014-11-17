<?php namespace OpenCFP\Console; 

use Symfony\Component\Console\Application as ConsoleApplication;
use OpenCFP\Application as ApplicationContainer;

class Application extends ConsoleApplication
{
    /**
     * @var ApplicationContainer
     */
    protected $app;

    public function __construct(ApplicationContainer $app)
    {
        parent::__construct('OpenCFP');
        $this->app = $app;
    }
}