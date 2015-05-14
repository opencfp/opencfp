<?php

namespace OpenCFP\Http\API;

use OpenCFP\Application;
use Symfony\Component\HttpFoundation\JsonResponse as Response;

class ApiController 
{
    protected $statusCode = Response::HTTP_OK;

    /**
     * @param $status
     *
     * @return $this
     */
    public function setStatusCode($status)
    {
        $this->statusCode = $status;
        return $this;
    }

    /**
     * @return int
     */
    public function getStatusCode()
    {
        return $this->statusCode;
    }

    /**
     * Returns a rendered Twig response.
     *
     * @param array $data
     * @param array $headers
     *
     * @return mixed
     */
    public function respond(array $data, array $headers = [])
    {
        return new Response($data, $this->getStatusCode(), $headers);
    }

    /**
     * @param string $message
     *
     * @return mixed
     */
    public function respondUnauthorized($message = 'Unauthorized')
    {
        return $this->setStatusCode(Response::HTTP_UNAUTHORIZED)->respondWithError($message);
    }

    /**
     * @param string $message
     *
     * @return mixed
     */
    public function respondForbidden($message = 'Forbidden')
    {
        return $this->setStatusCode(Response::HTTP_FORBIDDEN)->respondWithError($message);
    }

    /**
     * @param string $message
     *
     * @return mixed
     */
    public function respondNotFound($message = 'Resource not found')
    {
        return $this->setStatusCode(Response::HTTP_NOT_FOUND)->respond($message);
    }

    /**
     * @param string $message
     *
     * @return mixed
     */
    public function respondInternalError($message = 'Internal server error')
    {
        return $this->setStatusCode(Response::HTTP_INTERNAL_SERVER_ERROR)->respondWithError($message);
    }

    /**
     * @param $message
     *
     * @return mixed
     */
    public function respondWithError($message)
    {
        if ($this->statusCode === Response::HTTP_OK) {
            trigger_error(
                "You need to stellar reason to error with a 200 HTTP status code, Mr. Spock.",
                E_USER_WARNING
            );
        }
        return $this->respond([
            'message' => $message,
        ]);
    }
} 