<?php

declare(strict_types=1);

/**
 * Copyright (c) 2013-2017 OpenCFP
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @see https://github.com/opencfp/opencfp
 */

namespace OpenCFP\Http\Controller;

use OpenCFP\Application\NotAuthorizedException;
use OpenCFP\Application\Speakers;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig_Environment;

class TalkController extends BaseController
{
    /**
     * @var Speakers
     */
    private $speakers;

    public function __construct(Speakers $speakers, Twig_Environment $twig, UrlGeneratorInterface $urlGenerator)
    {
        $this->speakers = $speakers;

        parent::__construct($twig, $urlGenerator);
    }

    /**
     * Controller action for viewing a specific talk
     *
     * @param Request $request
     *
     * @return Response
     */
    public function viewAction(Request $request)
    {
        try {
            $talkId = (int) $request->get('id');
            $talk   = $this->speakers->getTalk($talkId);
        } catch (NotAuthorizedException $e) {
            return $this->redirectTo('dashboard');
        }

        return $this->render('talk/view.twig', \compact('talkId', 'talk'));
    }
}
