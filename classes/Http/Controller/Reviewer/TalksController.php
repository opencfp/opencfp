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

namespace OpenCFP\Http\Controller\Reviewer;

use OpenCFP\Domain\Talk\TalkHandler;
use OpenCFP\Domain\ValidationException;
use OpenCFP\Http\Controller\BaseController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig_Environment;

class TalksController extends BaseController
{
    /**
     * @var TalkHandler
     */
    private $talkHandler;

    public function __construct(TalkHandler $talkHandler, Twig_Environment $twig, UrlGeneratorInterface $urlGenerator)
    {
        $this->talkHandler = $talkHandler;

        parent::__construct($twig, $urlGenerator);
    }

    public function rateAction(Request $request): Response
    {
        try {
            $this->validate($request, [
                'rating' => 'required|integer',
            ]);

            $content = (string) $this->talkHandler
                ->grabTalk((int) $request->get('id'))
                ->rate((int) $request->get('rating'));
        } catch (ValidationException $e) {
            $content = '';
        }

        return new Response($content);
    }
}
