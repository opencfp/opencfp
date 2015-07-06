<?php

use Mockery\MockInterface;
use OpenCFP\Application\Speakers;
use OpenCFP\Domain\Entity\User;
use OpenCFP\Domain\Speaker\SpeakerProfile;
use OpenCFP\Http\API\ProfileController;
use Mockery as m;
use Symfony\Component\HttpFoundation\Request;

class ProfileApiControllerTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var ProfileController
     */
    private $sut;

    /**
     * @var Speakers | MockInterface
     */
    private $speakers;

    protected function setup()
    {
        $this->speakers = m::mock('OpenCFP\Application\Speakers');
        $this->sut = new ProfileController($this->speakers);
    }

    /** @test */
    public function it_shows_a_speaker_profile()
    {
        $this->speakers->shouldReceive('findProfile')
            ->andReturn($this->someSpeakerProfile());

        $response = $this->sut->handleShowSpeakerProfile($this->getRequest());

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertContains('Hamburglar', $response->getContent());
    }

    /** @test */
    public function it_responds_unauthorized_when_no_authentication_provided()
    {
        $this->speakers->shouldReceive('findProfile')
            ->andThrow('OpenCFP\Domain\Services\NotAuthenticatedException');

        $response = $this->sut->handleShowSpeakerProfile($this->getRequest());

        $this->assertEquals(401, $response->getStatusCode());
        $this->assertContains('Unauthorized', $response->getContent());
    }

    /** @test */
    public function it_responds_internal_error_when_something_bad_happens()
    {
        $this->speakers->shouldReceive('findProfile')
            ->andThrow(new Exception('Zomgz it blew up somehow.'));

        $response = $this->sut->handleShowSpeakerProfile($this->getRequest());

        $this->assertEquals(500, $response->getStatusCode());
        $this->assertContains('Zomgz it blew up somehow', $response->getContent());
    }

    private function getRequest(array $data = [])
    {
        $request = Request::create('');
        $request->request->replace($data);

        return $request;
    }

    private function someSpeakerProfile()
    {
        return new SpeakerProfile(new User(['first_name' => 'Hamburglar']));
    }
}
