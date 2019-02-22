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
use OpenCFP\Domain\Model;
use OpenCFP\Domain\Services;
use OpenCFP\Http\View;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation;
use Symfony\Component\Routing;

final class EditAction
{
    /**
     * @var Services\Authentication
     */
    private $authentication;

    /**
     * @var View\TalkHelper
     */
    private $talkHelper;

    /**
     * @var CallForPapers
     */
    private $callForPapers;

    /**
     * @var Routing\Generator\UrlGeneratorInterface
     */
    private $urlGenerator;

    public function __construct(
        Services\Authentication $authentication,
        View\TalkHelper $talkHelper,
        CallForPapers $callForPapers,
        Routing\Generator\UrlGeneratorInterface $urlGenerator
    ) {
        $this->authentication = $authentication;
        $this->talkHelper     = $talkHelper;
        $this->callForPapers  = $callForPapers;
        $this->urlGenerator   = $urlGenerator;
    }

    /**
     * @Template("talk/edit.twig")
     */
    public function __invoke(HttpFoundation\Request $request)
    {
        $talkId = (int) $request->get('id');

        if (!$this->callForPapers->isOpen()) {
            $request->getSession()->set('flash', [
                'type'  => 'error',
                'short' => 'Read Only',
                'ext'   => 'You cannot edit talks once the call for papers has ended',
            ]);

            $url = $this->urlGenerator->generate('talk_view', [
                'id' => $talkId,
            ]);

            return new HttpFoundation\RedirectResponse($url);
        }

        if (empty($talkId)) {
            $url = $this->urlGenerator->generate('dashboard');

            return new HttpFoundation\RedirectResponse($url);
        }

        $userId = $this->authentication->user()->getId();

        $talk = Model\Talk::find($talkId);

        if (!$talk instanceof Model\Talk || (int) $talk['user_id'] !== $userId) {
            $url = $this->urlGenerator->generate('dashboard');

            return new HttpFoundation\RedirectResponse($url);
        }

        return [
            'formAction'     => $this->urlGenerator->generate('talk_update'),
            'talkCategories' => $this->talkHelper->getTalkCategories(),
            'talkTypes'      => $this->talkHelper->getTalkTypes(),
            'talkLevels'     => $this->talkHelper->getTalkLevels(),
            'id'             => $talkId,
            'title'          => \html_entity_decode($talk['title']),
            'description'    => \html_entity_decode($talk['description']),
            'type'           => $talk['type'],
            'level'          => $talk['level'],
            'category'       => $talk['category'],
            'desired'        => $talk['desired'],
            'slides'         => $talk['slides'],
            'other'          => $talk['other'],
            'sponsor'        => $talk['sponsor'],
            'buttonInfo'     => 'Update my talk!',
        ];
    }
}
