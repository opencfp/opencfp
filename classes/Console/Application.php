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

namespace OpenCFP\Console;

use OpenCFP\Application as ApplicationContainer;
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
            new HelpCommand(),
            new ListCommand(),
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
