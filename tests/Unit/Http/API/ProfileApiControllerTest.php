<?php

namespace OpenCFP\Test\Unit\Http\API;

use Mockery as m;
use Mockery\MockInterface;
use OpenCFP\Application\Speakers;
use OpenCFP\Domain\Entity\User;
use OpenCFP\Domain\Speaker\SpeakerProfile;
use OpenCFP\Http\API\ProfileController;
use Symfony\Component\HttpFoundation;
use Symfony\Component\HttpFoundation\Request;

/**
 * @covers \OpenCFP\Http\API\ProfileController
 */
class ProfileApiControllerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ProfileController
     */
    private $sut;

    /**
     * @var Speakers | MockInterface
     */
    private $speakers;

    protected function setUp()
    {
        $this->speakers = m::mock(\OpenCFP\Application\Speakers::class);
        $this->sut      = new ProfileController($this->speakers);
    }

    public function it_shows_a_speaker_profile()
    {
        $this->speakers->shouldReceive('findProfile')
            ->andReturn($this->someSpeakerProfile());

        $response = $this->sut->handleShowSpeakerProfile($this->getRequest());

        $this->assertEquals(HttpFoundation\Response::HTTP_OK, $response->getStatusCode());
        $this->assertContains('Hamburglar', $response->getContent());
    }

    /** @test */
    public function it_responds_unauthorized_when_no_authentication_provided()
    {
        $this->speakers->shouldReceive('findProfile')
            ->andThrow(\OpenCFP\Domain\Services\NotAuthenticatedException::class);

        $response = $this->sut->handleShowSpeakerProfile($this->getRequest());

        $this->assertEquals(HttpFoundation\Response::HTTP_UNAUTHORIZED, $response->getStatusCode());
        $this->assertContains('Unauthorized', $response->getContent());
    }

    /** @test */
    public function it_responds_internal_error_when_something_bad_happens()
    {
        $this->speakers->shouldReceive('findProfile')
            ->andThrow(new \Exception('Zomgz it blew up somehow.'));

        $response = $this->sut->handleShowSpeakerProfile($this->getRequest());

        $this->assertEquals(HttpFoundation\Response::HTTP_INTERNAL_SERVER_ERROR, $response->getStatusCode());
        $this->assertContains('Zomgz it blew up somehow', $response->getContent());
    }

    //
    // Factory Methods
    //

    private function getRequest(array $data = []): Request
    {
        $request = Request::create('');
        $request->request->replace($data);

        return $request;
    }

    private function someSpeakerProfile(): SpeakerProfile
    {
        return new SpeakerProfile(new User(['first_name' => 'Hamburglar']));
    }
}
