<?php

declare(strict_types=1);

/**
 * Copyright (c) 2013-2018 OpenCFP
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @see https://github.com/opencfp/opencfp
 */

namespace OpenCFP\Http\Action\Reviewer\Speaker;

use OpenCFP\Domain\Model;
use OpenCFP\Domain\Services;
use Symfony\Component\HttpFoundation;
use Twig_Environment;

final class IndexAction
{
    /**
     * @var Twig_Environment
     */
    private $twig;

    public function __construct(Twig_Environment $twig)
    {
        $this->twig = $twig;
    }

    public function __invoke(HttpFoundation\Request $request): HttpFoundation\Response
    {
        $search = $request->get('search');

        $speakers = Model\User::search($search)->get()->toArray();

        $pagination = new Services\Pagination($speakers);

        $pagination->setCurrentPage($request->get('page'));

        $content = $this->twig->render('reviewer/speaker/index.twig', [
            'pagination' => $pagination->createView('/reviewer/speakers?'),
            'speakers'   => $pagination->getFanta(),
            'page'       => $pagination->getCurrentPage(),
            'search'     => $search ?: '',
        ]);

        return new HttpFoundation\Response($content);
    }
}
