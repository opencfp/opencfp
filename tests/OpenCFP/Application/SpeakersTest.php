<?php 

namespace OpenCFP\Application;

use Mockery as m;
use Mockery\MockInterface;
use OpenCFP\Domain\Entity\SpeakerRepository;
use OpenCFP\Domain\Entity\Talk;
use OpenCFP\Domain\Entity\User;

class SpeakersTest extends \PHPUnit_Framework_TestCase 
{
    const SPEAKER_ID = '1';

    /** @var Speakers */
    private $sut;

    /** @var SpeakerRepository | MockInterface */
    private $speakerRepository;

    protected function setUp(){
        parent::setUp();

        $this->speakerRepository = m::mock('OpenCFP\Domain\Speaker\SpeakerRepository');
        $this->sut = new Speakers($this->speakerRepository);
    }

    /** @test */
    public function it_provides_the_right_speaker_profile_when_asked()
    {
        $speaker = $this->getSpeaker();
        $this->trainStudentRepositoryToReturnSampleSpeaker($speaker);

        $profile = $this->sut->findProfile(self::SPEAKER_ID);

        $this->assertInstanceOf('OpenCFP\Domain\Speaker\SpeakerProfile', $profile);
        $this->assertEquals($speaker->email, $profile->getEmail());
        $this->assertEquals($speaker->first_name . ' ' . $speaker->last_name, $profile->getName());
    }

    /** @test */
    public function it_throws_an_exception_when_speaker_is_not_found()
    {
        $this->trainStudentRepositoryToThrowEntityNotFoundException();

        $this->setExpectedException('OpenCFP\Domain\EntityNotFoundException');
        $this->sut->findProfile('does not exist');
    }

    /** @test */
    public function it_retrieves_a_specific_talk_owned_by_speaker()
    {
    }

    /** @test */
    public function it_disallows_speakers_viewing_talks_other_than_their_own()
    {
        // We use relation to grab speakers talks. So if they have none, someone is doing
        // something screwy attempting to get a talk they should be able to.
        $this->trainStudentRepositoryToReturnSampleSpeaker($this->getSpeakerWithNoTalks());

        $this->setExpectedException('OpenCFP\Application\NotAuthorizedException');
        $this->sut->getTalk(self::SPEAKER_ID, 1);
    }

    /** @test */
    public function it_guards_if_spot_relation_ever_returns_talks_that_arent_owned_by_speaker()
    {
        $this->trainStudentRepositoryToReturnSampleSpeaker($this->getSpeakerFromMisbehavingSpot());

        $this->setExpectedException('OpenCFP\Application\NotAuthorizedException');
        $this->sut->getTalk(self::SPEAKER_ID, 1);
    }

    private function trainStudentRepositoryToReturnSampleSpeaker($speaker)
    {
        $this->speakerRepository->shouldReceive('findById')
            ->with(self::SPEAKER_ID)
            ->andReturn($speaker);
    }

    private function trainStudentRepositoryToThrowEntityNotFoundException()
    {
        $this->speakerRepository->shouldReceive('findById')
            ->andThrow('OpenCFP\Domain\EntityNotFoundException');
    }

    private function getSpeaker()
    {
        return new User([
            'id' => self::SPEAKER_ID,
            'email' => 'speaker@opencfp.org',
            'first_name' => 'Fake',
            'last_name' => 'Speaker'
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
                'user_id' => self::SPEAKER_ID + 1 // Not the speaker!
            ])
        );

        return $stub;
    }
}
 