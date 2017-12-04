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

use Mockery;
use OpenCFP\Application;
use OpenCFP\Domain\Services\Authentication;
use OpenCFP\Domain\Services\TalkRating\OneToTenRating;
use OpenCFP\Domain\Services\TalkRating\TalkRatingStrategy;
use OpenCFP\Domain\Services\TalkRating\YesNoRating;
use OpenCFP\Environment;
use OpenCFP\Test\Helper\Faker\GeneratorTrait;

/**
 * @covers \OpenCFP\Provider\TalkRatingProvider
 */
final class TalkRatingProviderTest extends \PHPUnit\Framework\TestCase
{
    use GeneratorTrait;

    /**
     * @dataProvider strategySettingsProvider
     */
    public function testItReturnsTheRightType($configInput, string $className)
    {
        //Set up our application object
        $app                 = new Application(__DIR__ . '/../../..', Environment::testing());
        $app['session.test'] = true;
        $auth                = Mockery::mock(Authentication::class);
        $auth->shouldReceive('userId')->once()->andReturn($this->getFaker()->randomNumber(1));
        $app[Authentication::class] = $auth;
        //Fake our config
        $config['application']['rating_system'] = $configInput;
        $app['config']                          = $config;
        //Check if we get the correct instance from the app
        $system = $app[TalkRatingStrategy::class];
        $this->assertInstanceOf($className, $system);
    }

    public function strategySettingsProvider(): array
    {
        return [
            'Input is yesno' => [
                'yesno',
                YesNoRating::class,
            ],
            'Input is onetoten' => [
                'onetoten',
                OneToTenRating::class,
            ],
            'Input is null' => [
                null,
                YesNoRating::class,
            ],
            'Input is empty string' => [
                '',
                YesNoRating::class,
            ],
            'Input is random giberish' => [
                'asdfasdfhj098yh',
                YesNoRating::class,
            ],
        ];
    }
}
