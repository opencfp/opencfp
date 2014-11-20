<?php 

namespace OpenCFP\Application;

use Mockery as m;
use Mockery\MockInterface;
use OpenCFP\Domain\Entity\SpeakerRepository;
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
            'email' => self::SPEAKER_ID,
            'first_name' => 'Fake',
            'last_name' => 'Speaker'
        ]);
    }
}
 