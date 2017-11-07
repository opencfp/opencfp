<?php

namespace OpenCFP\Http\API;

use OpenCFP\Application\Speakers;
use OpenCFP\Domain\Services\NotAuthenticatedException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class ProfileController extends ApiController
{
    /**
     * @var Speakers
     */
    private $speakers;

    public function __construct(Speakers $speakers)
    {
        $this->speakers = $speakers;
    }

    /**
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function handleShowSpeakerProfile(Request $request)
    {
        try {
            $profile = $this->speakers->findProfile();

            return $this->respond($profile->toArrayForApi());
        } catch (NotAuthenticatedException $e) {
            return $this->respondUnauthorized();
        } catch (\Exception $e) {
            return $this->respondInternalError($e->getMessage());
        }
    }
}
