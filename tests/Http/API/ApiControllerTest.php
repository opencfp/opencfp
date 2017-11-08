<?php

namespace OpenCFP\Test\Http\API;

use Symfony\Component\HttpFoundation;

class ApiControllerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var StubApiController
     */
    private $sut;

    protected function setUp()
    {
        $this->sut = new StubApiController();
    }

    /** @test */
    public function it_allows_developer_to_specify_a_status_code_for_response()
    {
        $this->sut->setStatusCode(HttpFoundation\Response::HTTP_OK);
        $this->assertEquals(HttpFoundation\Response::HTTP_OK, $this->sut->getStatusCode());
    }

    /** @test */
    public function it_can_send_a_simple_json_response()
    {
        $response = $this->sut->setStatusCode(HttpFoundation\Response::HTTP_OK)->respond(['message' => 'Huzzah']);

        $this->assertInstanceOf(\Symfony\Component\HttpFoundation\JsonResponse::class, $response);
        $this->assertJson($response->getContent());
        $this->assertEquals(HttpFoundation\Response::HTTP_OK, $response->getStatusCode());
        $this->assertContains('Huzzah', $response->getContent());
    }

    /**
     * @test
     */
    public function it_warns_when_successful_status_code_is_used_for_error()
    {
        $this->expectException(\PHPUnit\Framework\Exception::class);
        $this->sut->setStatusCode(HttpFoundation\Response::HTTP_OK)
            ->respondWithError('Error with success status code');
    }

    /** @test */
    public function it_responds_with_error_message_given_appropriate_status_code()
    {
        $response = $this->sut->setStatusCode(HttpFoundation\Response::HTTP_BAD_REQUEST)
            ->respondWithError('Some kind of bad request.');

        $this->assertInstanceOf(\Symfony\Component\HttpFoundation\JsonResponse::class, $response);
        $this->assertEquals(HttpFoundation\Response::HTTP_BAD_REQUEST, $response->getStatusCode());
        $this->assertContains('bad request', $response->getContent());
    }

    /**
     * @test
     * @dataProvider specializedResponseExamples
     */
    public function it_has_helpers_to_send_specialized_responses($type, $expectedStatus, $expectedDefaultMessage)
    {
        $methodName = "respond{$type}";

        $response = $this->sut->$methodName();

        $this->assertEquals($expectedStatus, $response->getStatusCode());
        $this->assertContains($expectedDefaultMessage, $response->getContent());
    }

    public function specializedResponseExamples()
    {
        return [
            ['BadRequest', HttpFoundation\Response::HTTP_BAD_REQUEST, 'Bad request'],
            ['Unauthorized', HttpFoundation\Response::HTTP_UNAUTHORIZED, 'Unauthorized'],
            ['Forbidden', HttpFoundation\Response::HTTP_FORBIDDEN, 'Forbidden'],
            ['NotFound', HttpFoundation\Response::HTTP_NOT_FOUND, 'Resource not found'],
            ['InternalError', HttpFoundation\Response::HTTP_INTERNAL_SERVER_ERROR, 'Internal server error'],
        ];
    }
}
