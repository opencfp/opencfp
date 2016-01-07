<?php

namespace OpenCFP\Application;

use Mockery as m;
use Mockery\MockInterface;
use OpenCFP\Domain\CallForProposal;
use OpenCFP\Domain\Entity\Talk;
use OpenCFP\Domain\Entity\User;
use OpenCFP\Domain\Services\EventDispatcher;
use OpenCFP\Domain\Services\IdentityProvider;
use OpenCFP\Domain\Speaker\SpeakerRepository;
use OpenCFP\Domain\Talk\TalkRepository;
use OpenCFP\Domain\Talk\TalkSubmission;

class SpeakersTest extends \PHPUnit_Framework_TestCase
{
    const SPEAKER_ID = '1';

    /** @var Speakers */
    private $sut;

    /** @var SpeakerRepository | MockInterface */
    private $speakerRepository;

    /** @var TalkRepository | MockInterface */
    private $talkRepository;

    /** @var IdentityProvider | MockInterface */
    private $identityProvider;

    /** @var CallForProposal | MockInterface */
    private $callForProposal;

    /** @var EventDispatcher | MockInterface */
    private $dispatcher;

    protected function setUp()
    {
        parent::setUp();

        $this->identityProvider = m::mock(\OpenCFP\Domain\Services\IdentityProvider::class);
        $this->speakerRepository = m::mock(\OpenCFP\Domain\Speaker\SpeakerRepository::class);
        $this->talkRepository = m::mock(\OpenCFP\Domain\Talk\TalkRepository::class);
        $this->callForProposal = m::mock(\OpenCFP\Domain\CallForProposal::class);
        $this->dispatcher = m::mock(\OpenCFP\Domain\Services\EventDispatcher::class);

        $this->sut = new Speakers($this->callForProposal, $this->identityProvider, $this->speakerRepository, $this->talkRepository, $this->dispatcher);
    }

    protected function tearDown()
    {
        parent::tearDown();
        m::close();
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
        $this->assertEquals($speaker->email, $profile->getEmail());
        $this->assertEquals($speaker->first_name . ' ' . $speaker->last_name, $profile->getName());
    }

    /** @test */
    public function it_throws_an_exception_when_speaker_is_not_found()
    {
        $this->trainStudentRepositoryToThrowEntityNotFoundException();

        $this->setExpectedException(\OpenCFP\Domain\EntityNotFoundException::class);
        $this->sut->findProfile();
    }

    /** @test */
    public function it_retrieves_a_specific_talk_owned_by_speaker()
    {
        $this->trainIdentityProviderToReturnSampleSpeaker($this->getSpeakerWithOneTalk());

        $talk = $this->sut->getTalk(1);

        $this->assertEquals('Testy Talk', $talk->title);
    }

    /** @test */
    public function it_disallows_speakers_viewing_talks_other_than_their_own()
    {
        // We use relation to grab speakers talks. So if they have none, someone is doing
        // something screwy attempting to get a talk they should be able to.
        $this->trainIdentityProviderToReturnSampleSpeaker($this->getSpeakerWithNoTalks());

        $this->setExpectedException(\OpenCFP\Application\NotAuthorizedException::class);
        $this->sut->getTalk(1);
    }

    /** @test */
    public function it_retrieves_all_talks_for_authenticated_speaker()
    {
        $this->identityProvider->shouldReceive('getCurrentUser')
            ->once()
            ->andReturn($this->getSpeakerWithManyTalks());

        $talks = $this->sut->getTalks();

        $this->assertEquals('Testy Talk', $talks[0]->title);
        $this->assertEquals('Another Talk', $talks[1]->title);
        $this->assertEquals('Yet Another Talk', $talks[2]->title);
    }

    /** @test */
    public function it_guards_if_spot_relation_ever_returns_talks_that_arent_owned_by_speaker()
    {
        $this->trainIdentityProviderToReturnSampleSpeaker($this->getSpeakerFromMisbehavingSpot());

        $this->setExpectedException(\OpenCFP\Application\NotAuthorizedException::class);
        $this->sut->getTalk(1);
    }

    //
    // Talk Submission
    //

    /** @test */
    public function it_should_allow_authenticated_speakers_to_submit_talks()
    {
        $this->callForProposal->shouldReceive('isOpen')
            ->once()
            ->andReturn(true);

        $this->identityProvider->shouldReceive('getCurrentUser')
            ->once()
            ->andReturn($this->getSpeaker());

        $this->talkRepository->shouldReceive('persist')
            ->with(m::type(\OpenCFP\Domain\Entity\Talk::class))
            ->once();

        $this->dispatcher->shouldReceive('dispatch')
            ->with('opencfp.talk.submit', m::type(\OpenCFP\Domain\Talk\TalkWasSubmitted::class))
            ->once();

        $submission = TalkSubmission::fromNative([
            'title' => 'Sample Talk',
            'description' => 'Some example talk for our submission',
            'type' => 'regular',
            'category' => 'api',
            'level' => 'mid',
        ]);

        /**
         * This should determine the current authenticated speaker, create a Talk from
         * the data in the TalkSubmission and then persist that Talk. It should dispatch
         * an event when a talk is submitted.
         */
        $this->sut->submitTalk($submission);
    }

    /** @test */
    public function it_doesnt_allow_talk_submissions_after_cfp_has_ended()
    {
        $this->callForProposal->shouldReceive('isOpen')
            ->once()
            ->andReturn(false);

        $this->setExpectedException('Exception', 'has ended');

        $submission = TalkSubmission::fromNative([
            'title' => 'Sample Talk',
            'description' => 'Some example talk for our submission',
            'type' => 'regular',
            'category' => 'api',
            'level' => 'mid',
        ]);

        $this->sut->submitTalk($submission);
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

    private function getSpeaker()
    {
        return new User([
            'id' => self::SPEAKER_ID,
            'email' => 'speaker@opencfp.org',
            'first_name' => 'Fake',
            'last_name' => 'Speaker',
        ]);
    }

    private function getSpeakerWithNoTalks()
    {
        // Set up stub speaker.
        $stub = m::mock('stdClass');

        // Set up talks.
        $stub->talks = m::mock('stdClass');
        $stub->talks->shouldReceive('where->execute->first')->andReturnNull();

        return $stub;
    }

    private function getSpeakerFromMisbehavingSpot()
    {
        // Set up stub speaker.
        $stub = m::mock('stdClass');
        $stub->id = self::SPEAKER_ID;

        // Set up talks.
        $stub->talks = m::mock('stdClass');
        $stub->talks->shouldReceive('where->execute->first')->andReturn(
            new Talk([
                'id' => 1,
                'title' => 'Testy Talk',
                'user_id' => self::SPEAKER_ID + 1, // Not the speaker!
            ])
        );

        return $stub;
    }

    private function getSpeakerWithOneTalk()
    {
        // Set up stub speaker.
        $stub = m::mock('stdClass');
        $stub->id = self::SPEAKER_ID;

        // Set up talks.
        $stub->talks = m::mock('stdClass');
        $stub->talks->shouldReceive('where->execute->first')->andReturn(
            new Talk([
                'id' => 1,
                'title' => 'Testy Talk',
                'user_id' => self::SPEAKER_ID,
            ])
        );

        return $stub;
    }

    private function getSpeakerWithManyTalks()
    {
        // Set up stub speaker.
        $stub = m::mock('stdClass');
        $stub->id = self::SPEAKER_ID;

        // Set up talks.
        $stub->talks = m::mock('stdClass');
        $stub->talks->shouldReceive('execute')->andReturn([
            new Talk([
                'id' => 1,
                'title' => 'Testy Talk',
                'user_id' => self::SPEAKER_ID,
            ]),
            new Talk([
                'id' => 2,
                'title' => 'Another Talk',
                'user_id' => self::SPEAKER_ID,
            ]),
            new Talk([
                'id' => 3,
                'title' => 'Yet Another Talk',
                'user_id' => self::SPEAKER_ID,
            ]),
        ]);

        return $stub;
    }
}
