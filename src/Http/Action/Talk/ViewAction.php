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

use OpenCFP\Application\NotAuthorizedException;
use OpenCFP\Application\Speakers;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation;
use Symfony\Component\Routing;

final class ViewAction
{
    /**
     * @var Speakers
     */
    private $speakers;

    /**
     * @var Routing\Generator\UrlGeneratorInterface
     */
    private $urlGenerator;

    public function __construct(
        Speakers $speakers,
        Routing\Generator\UrlGeneratorInterface $urlGenerator
    ) {
        $this->speakers     = $speakers;
        $this->urlGenerator = $urlGenerator;
    }

    /**
     * @Template("talk/view.twig")
     */
    public function __invoke(HttpFoundation\Request $request)
    {
        $talkId = (int) $request->get('id');

        try {
            $talk = $this->speakers->getTalk($talkId);
        } catch (NotAuthorizedException $exception) {
            $url = $this->urlGenerator->generate('dashboard');

            return new HttpFoundation\RedirectResponse($url);
        }

        return [
            'talkId' => $talkId,
            'talk'   => $talk,
        ];
    }
}
