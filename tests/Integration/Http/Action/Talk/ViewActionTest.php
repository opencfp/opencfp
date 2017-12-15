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

namespace OpenCFP\Test\Integration\Http\Action\Talk;

use Mockery as m;
use OpenCFP\Application\Speakers;
use OpenCFP\Domain\Model;
use OpenCFP\Test\Helper\RefreshDatabase;
use OpenCFP\Test\Integration\WebTestCase;

final class ViewActionTest extends WebTestCase
{
    use RefreshDatabase;

    /**
     * @test
     */
    public function willDisplayOwnTalk()
    {
        /** @var Model\Talk $talk */
        $talk = factory(Model\Talk::class, 1)->create()->first();

        $user = $talk->speaker->first();

        $speakers = m::mock(Speakers::class);
        $speakers->shouldReceive('getTalk')->andReturn($talk);

        $this->swap('application.speakers', $speakers);

        $url = \sprintf(
            '/talk/%d',
            $talk->id
        );

        $response = $this
            ->asLoggedInSpeaker($user->id)
            ->get($url);

        $this->assertResponseIsSuccessful($response);
        $this->assertResponseBodyContains($talk->title, $response);
    }
}
