<?php

use OpenCFP\Http\API\StubApiController;

class ApiControllerTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var StubApiController
     */
    private $sut;

    protected function setup()
    {
        $this->sut = new StubApiController();
    }

    /** @test */
    public function it_allows_developer_to_specify_a_status_code_for_response()
    {
        $this->sut->setStatusCode(200);
        $this->assertEquals(200, $this->sut->getStatusCode());
    }

    /** @test */
    public function it_can_send_a_simple_json_response()
    {
        $response = $this->sut->setStatusCode(200)->respond(['message' => 'Huzzah']);

        $this->assertInstanceOf('Symfony\Component\HttpFoundation\JsonResponse', $response);
        $this->assertJson($response->getContent());
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertContains('Huzzah', $response->getContent());
    }

    /** @test */
    public function it_warns_when_successful_status_code_is_used_for_error()
    {
        $this->setExpectedException('PHPUnit_Framework_Error_Warning');
        $this->sut->setStatusCode(200)
            ->respondWithError('Error with success status code');
    }

    /** @test */
    public function it_responds_with_error_message_given_appropriate_status_code()
    {
        $response = $this->sut->setStatusCode(400)
            ->respondWithError('Some kind of bad request.');

        $this->assertInstanceOf('Symfony\Component\HttpFoundation\JsonResponse', $response);
        $this->assertEquals(400, $response->getStatusCode());
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
            ['BadRequest', 400, 'Bad request'],
            ['Unauthorized', 401, 'Unauthorized'],
            ['Forbidden', 403, 'Forbidden'],
            ['NotFound', 404, 'Resource not found'],
            ['InternalError', 500, 'Internal server error'],
        ];
    }
}
