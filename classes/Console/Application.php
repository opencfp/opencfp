<?php namespace OpenCFP\Console;

use OpenCFP\Application as ApplicationContainer;
use OpenCFP\Console\Command\AdminDemoteCommand;
use OpenCFP\Console\Command\AdminPromoteCommand;
use OpenCFP\Console\Command\ClearCacheCommand;
use OpenCFP\Console\Command\UserCreateCommand;
use Symfony\Component\Console\Application as ConsoleApplication;
use Symfony\Component\Console\Command\HelpCommand;
use Symfony\Component\Console\Command\ListCommand;
use Symfony\Component\Console\Input\InputOption;

class Application extends ConsoleApplication
{
    /**
     * @var ApplicationContainer
     */
    protected $app;

    /**
     * Application constructor.
     * @param ApplicationContainer $app
     */
    public function __construct(ApplicationContainer $app)
    {
        parent::__construct('OpenCFP');

        $this->getDefinition()->addOption(new InputOption('env', '', InputOption::VALUE_REQUIRED, 'The environment the command should run in'));

        $this->app = $app;
    }

    /**
     * @return array
     */
    public function getDefaultCommands()
    {
        return [
            new HelpCommand,
            new ListCommand,
            new AdminPromoteCommand,
            new AdminDemoteCommand,
            new UserCreateCommand,
            new ClearCacheCommand,
        ];
    }

    /**
     * @return ApplicationContainer
     */
    public function getContainer()
    {
        return $this->app;
    }
}
