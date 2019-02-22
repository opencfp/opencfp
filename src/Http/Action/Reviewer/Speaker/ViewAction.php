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

namespace OpenCFP\Http\Action\Reviewer\Speaker;

use OpenCFP\Domain\Model;
use OpenCFP\Domain\Speaker;
use Symfony\Component\HttpFoundation;
use Symfony\Component\Routing;

final class ViewAction
{
    /**
     * @var array
     */
    private $reviewerUsers;

    /**
     * @var \Twig_Environment
     */
    private $twig;

    /**
     * @var Routing\Generator\UrlGeneratorInterface
     */
    private $urlGenerator;

    public function __construct(
        array $reviewerUsers,
        \Twig_Environment $twig,
        Routing\Generator\UrlGeneratorInterface $urlGenerator
    ) {
        $this->reviewerUsers = $reviewerUsers;
        $this->twig          = $twig;
        $this->urlGenerator  = $urlGenerator;
    }

    /**
     * @param HttpFoundation\Request $request
     *
     * @return HttpFoundation\Response
     */
    public function __invoke(HttpFoundation\Request $request): HttpFoundation\Response
    {
        $speaker = Model\User::where('id', $request->get('id'))->first();

        if (!$speaker instanceof Model\User) {
            $request->getSession()->set('flash', [
                'type'  => 'error',
                'short' => 'Error',
                'ext'   => 'Could not find requested speaker',
            ]);

            $url = $this->urlGenerator->generate('reviewer_speakers');

            return new HttpFoundation\RedirectResponse($url);
        }

        $talks = $speaker->talks()->get()->toArray();

        $content = $this->twig->render('reviewer/speaker/view.twig', [
            'speaker' => new Speaker\SpeakerProfile(
                $speaker,
                $this->reviewerUsers
            ),
            'talks' => $talks,
            'page'  => $request->get('page'),
        ]);

        return new HttpFoundation\Response($content);
    }
}
