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

namespace OpenCFP\Http\Action\Talk;

use OpenCFP\Application\NotAuthorizedException;
use OpenCFP\Application\Speakers;
use Symfony\Component\HttpFoundation;
use Symfony\Component\Routing;
use Twig_Environment;

final class ViewAction
{
    /**
     * @var Speakers
     */
    private $speakers;

    /**
     * @var Twig_Environment
     */
    private $twig;

    /**
     * @var Routing\Generator\UrlGeneratorInterface
     */
    private $urlGenerator;

    public function __construct(
        Speakers $speakers,
        Twig_Environment $twig,
        Routing\Generator\UrlGeneratorInterface $urlGenerator
    ) {
        $this->speakers     = $speakers;
        $this->twig         = $twig;
        $this->urlGenerator = $urlGenerator;
    }

    public function __invoke(HttpFoundation\Request $request): HttpFoundation\Response
    {
        $talkId = (int) $request->get('id');

        try {
            $talk = $this->speakers->getTalk($talkId);
        } catch (NotAuthorizedException $exception) {
            $url = $this->urlGenerator->generate('dashboard');

            return new HttpFoundation\RedirectResponse($url);
        }

        $content = $this->twig->render('talk/view.twig', [
            'talkId' => $talkId,
            'talk'   => $talk,
        ]);

        return new HttpFoundation\Response($content);
    }
}
