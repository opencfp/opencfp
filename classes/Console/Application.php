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

use Symfony\Component\Console;

class Application extends Console\Application
{
    public function __construct()
    {
        parent::__construct('OpenCFP');

        $this->getDefinition()->addOption(new Console\Input\InputOption(
            'env',
            '',
            Console\Input\InputOption::VALUE_REQUIRED,
            'The environment the command should run in'
        ));
    }

    /**
     * @return array
     */
    public function getDefaultCommands()
    {
        return [
            new Console\Command\HelpCommand(),
            new Console\Command\ListCommand(),
        ];
    }
}
