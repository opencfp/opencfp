<?php

declare(strict_types=1);

/**
 * Copyright (c) 2013-2019 OpenCFP
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @see https://github.com/opencfp/opencfp
 */

namespace OpenCFP\Test\Unit\Application;

use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Mockery as m;
use Mockery\MockInterface;
use OpenCFP\Application\Speakers;
use OpenCFP\Domain\Model\Talk;
use OpenCFP\Domain\Model\User;
use OpenCFP\Domain\Services\IdentityProvider;

final class SpeakersTest extends \PHPUnit\Framework\TestCase
{
    use MockeryPHPUnitIntegration;

    public const SPEAKER_ID = '1';

    /** @var Speakers */
    private $sut;

    /** @var IdentityProvider | MockInterface */
    private $identityProvider;

    protected function setUp()
    {
        $this->identityProvider = m::mock(\OpenCFP\Domain\Services\IdentityProvider::class);

        $this->sut = new Speakers($this->identityProvider);
    }

    //
    // Speaker Profiles & Such
    //

    /** @test */
    public function it_provides_the_right_speaker_profile_when_asked()
    {
        $speaker = $this->getSpeaker();
        $this->trainIdentityProviderToReturnSampleSpeaker($speaker);

        $profile = $this->sut->findProfile();

        $this->assertInstanceOf(\OpenCFP\Domain\Speaker\SpeakerProfile::class, $profile);
        $this->assertSame($speaker->email, $profile->getEmail());
        $this->assertSame($speaker->first_name . ' ' . $speaker->last_name, $profile->getName());
    }

    /**
     * @test
     */
    public function it_throws_an_exception_when_speaker_is_not_found()
    {
        $this->trainStudentRepositoryToThrowEntityNotFoundException();

        $this->expectException(\OpenCFP\Domain\EntityNotFoundException::class);

        $this->sut->findProfile();
    }

    /**
     * @test
     */
    public function it_retrieves_a_specific_talk_owned_by_speaker()
    {
        $this->trainIdentityProviderToReturnSampleSpeaker($this->getSpeakerWithOneTalk());

        $talk = $this->sut->getTalk(1);

        $this->assertSame('Testy Talk', $talk->title);
    }

    /**
     * @test
     */
    public function it_disallows_speakers_viewing_talks_other_than_their_own()
    {
        // We use relation to grab speakers talks. So if they have none, someone is doing
        // something screwy attempting to get a talk they should be able to.
        $this->trainIdentityProviderToReturnSampleSpeaker($this->getSpeakerWithNoTalks());

        $this->expectException(\OpenCFP\Application\NotAuthorizedException::class);

        $this->sut->getTalk(1);
    }

    /** @test */
    public function it_retrieves_all_talks_for_authenticated_speaker()
    {
        $this->identityProvider->shouldReceive('getCurrentUser')
            ->once()
            ->andReturn($this->getSpeakerWithManyTalks());

        $talks = $this->sut->getTalks();

        $this->assertSame('Testy Talk', $talks[0]->title);
        $this->assertSame('Another Talk', $talks[1]->title);
        $this->assertSame('Yet Another Talk', $talks[2]->title);
    }

    /**
     * @test
     */
    public function it_guards_if_relation_ever_returns_talks_that_arent_owned_by_speaker()
    {
        $this->trainIdentityProviderToReturnSampleSpeaker($this->getSpeakerFromMisbehavingRelation());

        $this->expectException(\OpenCFP\Application\NotAuthorizedException::class);

        $this->sut->getTalk(1);
    }

    //
    // Test Double Helpers
    //

    private function trainIdentityProviderToReturnSampleSpeaker($speaker)
    {
        $this->identityProvider->shouldReceive('getCurrentUser')
            ->once()
            ->andReturn($speaker);
    }

    private function trainStudentRepositoryToThrowEntityNotFoundException()
    {
        $this->identityProvider->shouldReceive('getCurrentUser')
            ->andThrow(\OpenCFP\Domain\EntityNotFoundException::class);
    }

    private function getSpeaker(): User
    {
        return new User([
            'id'         => self::SPEAKER_ID,
            'email'      => 'speaker@opencfp.org',
            'first_name' => 'Fake',
            'last_name'  => 'Speaker',
        ]);
    }

    private function getSpeakerWithNoTalks(): \stdClass
    {
        // Set up stub speaker.
        $stub = m::mock(\stdClass::class);
        $stub->shouldReceive('talks')->andReturnSelf();
        $stub->shouldReceive('find')->andReturnNull();

        return $stub;
    }

    private function getSpeakerFromMisbehavingRelation(): \stdClass
    {
        // Set up stub speaker.
        $stub     = m::mock(\stdClass::class);
        $stub->id = self::SPEAKER_ID;

        // Set up talks.
        $talk = m::mock(\stdClass::class);
        $talk->shouldReceive('find')->andReturn(
            new Talk([
                'id'      => 1,
                'title'   => 'Testy Talk',
                'user_id' => self::SPEAKER_ID + 1, // Not the speaker!
            ])
        );
        $stub->shouldReceive('talks')->andReturn($talk);

        return $stub;
    }

    private function getSpeakerWithOneTalk(): \stdClass
    {
        // Set up stub speaker.
        $stub     = m::mock(\stdClass::class);
        $stub->id = self::SPEAKER_ID;

        // Set up talks.
        $talk = m::mock(\stdClass::class);
        $talk->shouldReceive('find')->andReturn(
            new Talk([
                'id'      => 1,
                'title'   => 'Testy Talk',
                'user_id' => self::SPEAKER_ID,
            ])
        );
        $stub->shouldReceive('talks')->andReturn($talk);

        return $stub;
    }

    private function getSpeakerWithManyTalks(): \stdClass
    {
        // Set up stub speaker.
        $stub     = m::mock(\stdClass::class);
        $stub->id = self::SPEAKER_ID;

        // Set up talks.
        $stub->talks = [
            new Talk([
                'id'      => 1,
                'title'   => 'Testy Talk',
                'user_id' => self::SPEAKER_ID,
            ]),
            new Talk([
                'id'      => 2,
                'title'   => 'Another Talk',
                'user_id' => self::SPEAKER_ID,
            ]),
            new Talk([
                'id'      => 3,
                'title'   => 'Yet Another Talk',
                'user_id' => self::SPEAKER_ID,
            ]),
        ];

        return $stub;
    }
}
