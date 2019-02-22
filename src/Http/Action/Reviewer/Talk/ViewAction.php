<?php

declare(strict_types=1);

/**
 * Copyright (c) 2013-2019 OpenCFP
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @see https://github.com/opencfp/opencfp
 */

namespace OpenCFP\Http\Action\Reviewer\Talk;

use OpenCFP\Domain\Talk;
use Symfony\Component\HttpFoundation;
use Symfony\Component\Routing;
use Twig_Environment;

final class ViewAction
{
    /**
     * @var Talk\TalkHandler
     */
    private $talkHandler;

    /**
     * @var Twig_Environment
     */
    private $twig;

    /**
     * @var Routing\Generator\UrlGeneratorInterface
     */
    private $urlGenerator;

    public function __construct(
        Talk\TalkHandler $talkHandler,
        Twig_Environment $twig,
        Routing\Generator\UrlGeneratorInterface $urlGenerator
    ) {
        $this->talkHandler  = $talkHandler;
        $this->twig         = $twig;
        $this->urlGenerator = $urlGenerator;
    }

    public function __invoke(HttpFoundation\Request $request): HttpFoundation\Response
    {
        $this->talkHandler->grabTalk((int) $request->get('id'));

        if (!$this->talkHandler->view()) {
            $request->getSession()->set('flash', [
                'type'  => 'error',
                'short' => 'Error',
                'ext'   => 'Could not find requested talk',
            ]);

            $url = $this->urlGenerator->generate('reviewer_talks');

            return new HttpFoundation\RedirectResponse($url);
        }

        $content = $this->twig->render('reviewer/talks/view.twig', [
            'talk' => $this->talkHandler->getProfile(),
        ]);

        return new HttpFoundation\Response($content);
    }
}
