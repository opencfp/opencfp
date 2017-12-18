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

use OpenCFP\Domain\Model\User;
use OpenCFP\Domain\Services\Pagination;
use OpenCFP\Http\Controller\BaseController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class SpeakersController extends BaseController
{
    public function indexAction(Request $request): Response
    {
        $search   = $request->get('search');
        $speakers = User::search($search)->get()->toArray();
        // Set up our page stuff
        $pagerfanta = new Pagination($speakers);
        $pagerfanta->setCurrentPage($request->get('page'));
        $pagination = $pagerfanta->createView('/reviewer/speakers?');

        return $this->render('reviewer/speaker/index.twig', [
            'pagination' => $pagination,
            'speakers'   => $pagerfanta->getFanta(),
            'page'       => $pagerfanta->getCurrentPage(),
            'search'     => $search ?: '',
        ]);
    }
}
