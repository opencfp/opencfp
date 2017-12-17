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

namespace OpenCFP\Test\Integration\Provider;

use OpenCfp\Application;
use OpenCFP\Http\View\TalkHelper;
use OpenCFP\Provider\TalkHelperProvider;
use PHPUnit\Framework;

final class TalkHelperProviderTest extends Framework\TestCase
{
    public function testInjectsDefaultCategoriesIfNoneConfigured()
    {
        $app = $this->createApplicationWithEmptyConfiguration();

        $app->register(new TalkHelperProvider());

        /** @var TalkHelper $helper */
        $helper = $app[TalkHelper::class];

        $this->assertInstanceOf(TalkHelper::class, $helper);

        $defaultCategories = [
            'api'                => 'APIs (REST, SOAP, etc.)',
            'continuousdelivery' => 'Continuous Delivery',
            'database'           => 'Database',
            'development'        => 'Development',
            'devops'             => 'Devops',
            'framework'          => 'Framework',
            'ibmi'               => 'IBMi',
            'javascript'         => 'JavaScript',
            'security'           => 'Security',
            'testing'            => 'Testing',
            'uiux'               => 'UI/UX',
            'other'              => 'Other',
        ];

        $this->assertSame($defaultCategories, $helper->getTalkCategories());
    }
    
    public function testInjectsDefaultLevelsIfNoneConfigured()
    {
        $app = $this->createApplicationWithEmptyConfiguration();

        $app->register(new TalkHelperProvider());

        /** @var TalkHelper $helper */
        $helper = $app[TalkHelper::class];

        $this->assertInstanceOf(TalkHelper::class, $helper);

        $defaultLevels = [
            'entry'    => 'Entry level',
            'mid'      => 'Mid-level',
            'advanced' => 'Advanced',
        ];

        $this->assertSame($defaultLevels, $helper->getTalkLevels());
    }

    public function testInjectsDefaultTypesIfNoneConfigured()
    {
        $app = $this->createApplicationWithEmptyConfiguration();

        $app->register(new TalkHelperProvider());

        /** @var TalkHelper $helper */
        $helper = $app[TalkHelper::class];

        $this->assertInstanceOf(TalkHelper::class, $helper);

        $defaultTypes = [
            'regular'  => 'Regular',
            'tutorial' => 'Tutorial',
        ];

        $this->assertSame($defaultTypes, $helper->getTalkTypes());
    }

    private function createApplicationWithEmptyConfiguration(): Application
    {
        $reflection = new \ReflectionClass(Application::class);

        $application = $reflection->newInstanceWithoutConstructor();

        $application['config'] = [];

        return $application;
    }
}
