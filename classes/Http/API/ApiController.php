<?php

namespace OpenCFP\Http\API;

use OpenCFP\ContainerAware;
use Symfony\Component\HttpFoundation\JsonResponse as Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class ApiController
{
    use ContainerAware;

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
     * @param mixed $data
     * @param array $headers
     *
     * @return Response
     */
    public function respond($data, array $headers = [])
    {
        return new Response($data, $this->getStatusCode(), $headers);
    }

    /**
     * @param string $message
     *
     * @return Response
     */
    public function respondBadRequest($message = 'Bad request')
    {
        return $this->setStatusCode(Response::HTTP_BAD_REQUEST)->respondWithError($message);
    }

    /**
     * @param string $message
     *
     * @return Response
     */
    public function respondUnauthorized($message = 'Unauthorized')
    {
        return $this->setStatusCode(Response::HTTP_UNAUTHORIZED)->respondWithError($message);
    }

    /**
     * @param string $message
     *
     * @return Response
     */
    public function respondForbidden($message = 'Forbidden')
    {
        return $this->setStatusCode(Response::HTTP_FORBIDDEN)->respondWithError($message);
    }

    /**
     * @param string $message
     *
     * @return Response
     */
    public function respondNotFound($message = 'Resource not found')
    {
        return $this->setStatusCode(Response::HTTP_NOT_FOUND)->respond($message);
    }

    /**
     * @param string $message
     *
     * @return Response
     */
    public function respondInternalError($message = 'Internal server error')
    {
        return $this->setStatusCode(Response::HTTP_INTERNAL_SERVER_ERROR)->respondWithError($message);
    }

    /**
     * @param $message
     *
     * @return Response
     */
    public function respondWithError($message)
    {
        if ($this->statusCode === Response::HTTP_OK) {
            trigger_error(
                'You need to stellar reason to error with a 200 HTTP status code, Mr. Spock.',
                E_USER_WARNING
            );
        }

        return $this->respond([
            'message' => $message,
        ]);
    }

    /**
     * Generate an absolute url from a route name.
     *
     * @param string $route
     * @param array  $parameters
     *
     * @return string the generated URL
     */
    public function url($route, $parameters = [])
    {
        return $this->service('url_generator')->generate($route, $parameters, UrlGeneratorInterface::ABSOLUTE_URL);
    }

    /**
     * @param string $route  Route name to redirect to
     * @param int    $status
     *
     * @return RedirectResponse
     */
    public function redirectTo($route, $status = Response::HTTP_FOUND)
    {
        return $this->app->redirect($this->url($route), $status);
    }
}
