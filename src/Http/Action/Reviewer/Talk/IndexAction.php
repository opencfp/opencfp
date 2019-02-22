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

use OpenCFP\Domain\Services;
use OpenCFP\Domain\Talk;
use OpenCFP\Http\View;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation;

final class IndexAction
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
     * @var Talk\TalkFilter
     */
    private $talkFilter;

    public function __construct(Services\Authentication $authentication, View\TalkHelper $talkHelper, Talk\TalkFilter $talkFilter)
    {
        $this->authentication = $authentication;
        $this->talkHelper     = $talkHelper;
        $this->talkFilter     = $talkFilter;
    }

    /**
     * @Template("reviewer/talks/index.twig")
     *
     * @param HttpFoundation\Request $request
     *
     * @throws Services\NotAuthenticatedException
     *
     * @return array
     */
    public function __invoke(HttpFoundation\Request $request): array
    {
        $reviewerId = $this->authentication->user()->getId();

        $options = [
            'order_by' => $request->get('order_by'),
            'sort'     => $request->get('sort'),
        ];

        $formattedTalks = $this->talkFilter->getTalks(
            $reviewerId,
            $request->get('filter'),
            $request->get('category'),
            $request->get('type'),
            $options
        );

        $perPage = (int) $request->get('per_page') ?: 20;

        $pagination = new Services\Pagination(
            $formattedTalks,
            $perPage
        );

        $pagination->setCurrentPage($request->get('page'));

        return [
            'pagination' => $pagination->createView(
                '/reviewer/talks?',
                $request->query->all()
            ),
            'talks'          => $pagination->getFanta(),
            'page'           => $pagination->getCurrentPage(),
            'current_page'   => $request->getRequestUri(),
            'totalRecords'   => \count($formattedTalks),
            'filter'         => $request->get('filter'),
            'category'       => $request->get('category'),
            'type'           => $request->get('type'),
            'talkCategories' => $this->talkHelper->getTalkCategories(),
            'talkTypes'      => $this->talkHelper->getTalkTypes(),
            'per_page'       => $perPage,
            'sort'           => $request->get('sort'),
            'order_by'       => $request->get('order_by'),
        ];
    }
}
