<?php

namespace OpenCFP\Http\API;

use OpenCFP\Application;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\JsonResponse as Response;

class ApiController 
{
    /**
     * @var Application
     */
    protected $app;

    protected $statusCode = Response::HTTP_OK;

    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    public function setStatusCode($status)
    {
        $this->statusCode = $status;
        return $this;
    }

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

    public function respondUnauthorized($message = 'Unauthorized')
    {
        return $this->setStatusCode(Response::HTTP_UNAUTHORIZED)->respondWithError($message);
    }

    public function respondForbidden($message = 'Forbidden')
    {
        return $this->setStatusCode(Response::HTTP_FORBIDDEN)->respondWithError($message);
    }

    public function respondNotFound($message = 'Resource not found')
    {
        return $this->setStatusCode(Response::HTTP_NOT_FOUND)->respond($message);
    }

    public function respondInternalError($message = 'Internal server error')
    {
        return $this->setStatusCode(Response::HTTP_INTERNAL_SERVER_ERROR)->respondWithError($message);
    }

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

    /**
     * @param string $route  Route name to redirect to
     * @param int    $status
     *
     * @return RedirectResponse
     */
    public function redirectTo($route, $status = 302)
    {
        return $this->app->redirect($this->url($route), $status);
    }
} 