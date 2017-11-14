<?php

namespace OpenCFP\Http\OAuth;

use OpenCFP\Domain\Services\RandomStringGenerator;
use OpenCFP\Http\API\ApiController;
use Spot\Mapper;
use Symfony\Component\HttpFoundation\Request;

class ClientRegistrationController extends ApiController
{
    /**
     * @var Mapper
     */
    private $clients;

    /**
     * @var Mapper
     */
    private $endpoints;

    /**
     * @var RandomStringGenerator
     */
    private $generator;

    public function __construct(Mapper $clients, Mapper $endpoints, RandomStringGenerator $generator)
    {
        $this->clients   = $clients;
        $this->endpoints = $endpoints;
        $this->generator = $generator;
    }

    /**
     * POST /oauth/clients
     */
    public function registerClient(Request $request)
    {
        $clientIdentifier = $this->generator->generate(40);
        $clientSecret     = $this->generator->generate(40);

        try {
            $client = $this->clients->create([
                'id'     => $clientIdentifier,
                'secret' => $clientSecret,
                'name'   => $request->get('name'),
            ]);

            foreach ($request->get('redirect_uris') as $uri) {
                $this->endpoints->create([
                    'client_id'    => $clientIdentifier,
                    'redirect_uri' => $uri,
                ]);
            }

            return $this->respond($client->toArrayForApi());
        } catch (\Exception $e) {
            return $this->respondBadRequest();
        }
    }
}
