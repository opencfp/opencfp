<?php

namespace OpenCFP\Http\API;

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

    public function handleSubmitTalk(Request $request)
    {
        return 'not implemented';
    }

    public function handleViewTalk(Request $request)
    {
        return 'not implemented';
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