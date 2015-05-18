<?php

namespace OpenCFP\Http\API;

use Exception;
use OpenCFP\Application\Speakers;
use OpenCFP\Domain\Services\NotAuthenticatedException;
use OpenCFP\Domain\Talk\InvalidTalkSubmissionException;
use OpenCFP\Domain\Talk\TalkSubmission;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class TalkController extends ApiController
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
     * @throws Exception
     */
    public function handleSubmitTalk(Request $request)
    {
        try {
            $submission = TalkSubmission::fromNative($request->request->all());

            $talk = $this->speakers->submitTalk($submission);

            return $this
                ->setStatusCode(Response::HTTP_CREATED)
                ->respond($talk->toArrayForApi());

        } catch (InvalidTalkSubmissionException $e) {
            return $this->setStatusCode(Response::HTTP_BAD_REQUEST)->respondWithError($e->getMessage());
        } catch (NotAuthenticatedException $e) {
            return $this->respondUnauthorized();
        } catch (Exception $e) {
            return $this->respondInternalError($e->getMessage());
        }
    }

    public function handleViewAllTalks(Request $request)
    {
        try {
            $talks = $this->speakers->getTalks();

            // TODO Replace this crap with an object responsible for
            // the "Talk Resource" to/from json.
            $output = [];
            foreach($talks as $talk) {
                $output[] = $talk->toArrayForApi();
            }

            return $this
                ->setStatusCode(Response::HTTP_OK)
                ->respond($output);

        } catch (NotAuthenticatedException $e) {
            return $this->respondUnauthorized();
        }
    }

    public function handleViewTalk(Request $request, $id)
    {
        try {
            $talk = $this->speakers->getTalk($id);

            return $this
                ->setStatusCode(Response::HTTP_OK)
                ->respond($talk->toArrayForApi());

        } catch (NotAuthenticatedException $e) {
            return $this->respondUnauthorized();
        }
    }

    public function handleChangeTalk(Request $request)
    {
        return 'not implemented';
    }

    public function handleDeleteTalk(Request $request)
    {
        return 'not implemented';
    }
} 