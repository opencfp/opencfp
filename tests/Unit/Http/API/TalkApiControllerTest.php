<?php

namespace OpenCFP\Test\Unit\Http\API;

use Mockery as m;
use Mockery\MockInterface;
use OpenCFP\Application\Speakers;
use OpenCFP\Domain\Entity\Talk;
use OpenCFP\Domain\Talk\TalkSubmission;
use OpenCFP\Http\API\TalkController;
use Symfony\Component\HttpFoundation;
use Symfony\Component\HttpFoundation\Request;

/**
 * @coversNothing \OpenCFP\Http\API\TalkController
 */
class TalkApiControllerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var TalkController
     */
    private $sut;

    /**
     * @var Speakers | MockInterface
     */
    private $speakers;

    protected function setUp()
    {
        $this->speakers = m::mock(\OpenCFP\Application\Speakers::class);
        $this->sut      = new TalkController($this->speakers);
    }

    public function it_returns_created_response_when_talk_is_submitted()
    {
        $request = $this->getValidRequest();

        // Making these more or less to have speakers return something
        // sane to test output of 201 Created response. Should be the talk
        // we submitted!
        $submission = TalkSubmission::fromNative($request->request->all());
        $talk       = $submission->toTalk();

        $this->speakers->shouldReceive('submitTalk')
            ->once()
            ->with(m::type(\OpenCFP\Domain\Talk\TalkSubmission::class))
            ->andReturn($talk);

        $response = $this->sut->handleSubmitTalk($request);

        $this->assertEquals(HttpFoundation\Response::HTTP_CREATED, $response->getStatusCode());
        $this->assertContains('Happy Path Submission', $response->getContent());
    }

    /** @test */
    public function it_should_respond_with_bad_request_when_invalid()
    {
        $request = $this->getRequest(['title' => 'No description is bad, mmkay.']);

        $response = $this->sut->handleSubmitTalk($request);

        $this->assertEquals(HttpFoundation\Response::HTTP_BAD_REQUEST, $response->getStatusCode());
        $this->assertContains('The description of the talk must be included', $response->getContent());
    }

    /** @test */
    public function it_should_respond_with_unauthorized_when_no_authentication_provided()
    {
        $request = $this->getValidRequest();

        $this->speakers->shouldReceive('submitTalk')
            ->andThrow(\OpenCFP\Domain\Services\NotAuthenticatedException::class);

        $response = $this->sut->handleSubmitTalk($request);

        $this->assertEquals(HttpFoundation\Response::HTTP_UNAUTHORIZED, $response->getStatusCode());
    }

    public function it_should_respond_with_single_talk()
    {
        $this->speakers->shouldReceive('getTalk')->once()->andReturn(
            new Talk(['title' => 'Testy Talk'])
        );

        $response = $this->sut->handleViewTalk($this->getValidRequest(), 1);

        $this->assertEquals(HttpFoundation\Response::HTTP_OK, $response->getStatusCode());
        $this->assertContains('Testy Talk', $response->getContent());
    }

    /** @test */
    public function it_responds_unauthorized_when_viewing_single_talk_while_not_authenticated()
    {
        $this->speakers->shouldReceive('getTalk')
        ->andThrow(\OpenCFP\Domain\Services\NotAuthenticatedException::class);

        $response = $this->sut->handleViewTalk($this->getValidRequest(), 1);

        $this->assertEquals(HttpFoundation\Response::HTTP_UNAUTHORIZED, $response->getStatusCode());
        $this->assertContains('Unauthorized', $response->getContent());
    }

    public function it_should_respond_with_multiple_talks()
    {
        $this->speakers->shouldReceive('getTalks')->once()->andReturn([
            new Talk(['title' => 'Testy Talk']),
            new Talk(['title' => 'Another Talk']),
            new Talk(['title' => 'Yet Another Talk']),
        ]);

        $response = $this->sut->handleViewAllTalks($this->getValidRequest());

        $this->assertEquals(HttpFoundation\Response::HTTP_OK, $response->getStatusCode());
        $this->assertContains('Testy Talk', $response->getContent());
        $this->assertContains('Another Talk', $response->getContent());
        $this->assertContains('Yet Another Talk', $response->getContent());
    }

    /** @test */
    public function it_should_respond_unauthorized_when_no_authentication_provided()
    {
        $this->speakers->shouldReceive('getTalks')
            ->andThrow(\OpenCFP\Domain\Services\NotAuthenticatedException::class);

        $response = $this->sut->handleViewAllTalks($this->getValidRequest());

        $this->assertEquals(HttpFoundation\Response::HTTP_UNAUTHORIZED, $response->getStatusCode());
        $this->assertContains('Unauthorized', $response->getContent());
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

    private function getValidRequest(): Request
    {
        return $this->getRequest([
            'title'       => 'Happy Path Submission',
            'description' => 'I play by the rules.',
            'type'        => 'regular',
            'level'       => 'entry',
            'category'    => 'api',
        ]);
    }
}
