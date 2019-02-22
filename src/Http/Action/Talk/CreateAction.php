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

namespace OpenCFP\Http\Action\Talk;

use OpenCFP\Domain\CallForPapers;
use OpenCFP\Http\View;
use Symfony\Component\HttpFoundation;
use Symfony\Component\Routing;
use Twig_Environment;

final class CreateAction
{
    /**
     * @var View\TalkHelper
     */
    private $talkHelper;

    /**
     * @var CallForPapers
     */
    private $callForPapers;

    /**
     * @var Twig_Environment
     */
    private $twig;

    /**
     * @var Routing\Generator\UrlGeneratorInterface
     */
    private $urlGenerator;

    public function __construct(
        View\TalkHelper $talkHelper,
        CallForPapers $callForPapers,
        Twig_Environment $twig,
        Routing\Generator\UrlGeneratorInterface $urlGenerator
    ) {
        $this->talkHelper    = $talkHelper;
        $this->callForPapers = $callForPapers;
        $this->twig          = $twig;
        $this->urlGenerator  = $urlGenerator;
    }

    public function __invoke(HttpFoundation\Request $request): HttpFoundation\Response
    {
        if (!$this->callForPapers->isOpen()) {
            $request->getSession()->set('flash', [
                'type'  => 'error',
                'short' => 'Error',
                'ext'   => 'You cannot create talks once the call for papers has ended',
            ]);

            $url = $this->urlGenerator->generate('dashboard');

            return new HttpFoundation\RedirectResponse($url);
        }

        $content = $this->twig->render('talk/create.twig', [
            'formAction'     => $this->urlGenerator->generate('talk_create'),
            'talkCategories' => $this->talkHelper->getTalkCategories(),
            'talkTypes'      => $this->talkHelper->getTalkTypes(),
            'talkLevels'     => $this->talkHelper->getTalkLevels(),
            'title'          => $request->get('title'),
            'description'    => $request->get('description'),
            'type'           => $request->get('type'),
            'level'          => $request->get('level'),
            'category'       => $request->get('category'),
            'desired'        => $request->get('desired'),
            'slides'         => $request->get('slides'),
            'other'          => $request->get('other'),
            'sponsor'        => $request->get('sponsor'),
            'buttonInfo'     => 'Submit my talk!',
        ]);

        return new HttpFoundation\Response($content);
    }
}
