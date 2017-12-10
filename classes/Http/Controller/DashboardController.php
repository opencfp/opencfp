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

namespace OpenCFP\Http\Controller;

use OpenCFP\Application\Speakers;
use OpenCFP\Domain\Services\NotAuthenticatedException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig_Environment;

class DashboardController extends BaseController
{
    /**
     * @var Speakers
     */
    private $speakers;

    public function __construct(
        Speakers $speakers,
        Twig_Environment $twig,
        UrlGeneratorInterface $urlGenerator
    ) {
        $this->speakers = $speakers;

        parent::__construct($twig, $urlGenerator);
    }

    public function indexAction(): Response
    {
        try {
            return $this->render('dashboard.twig', [
                'profile' => $this->speakers->findProfile(),
            ]);
        } catch (NotAuthenticatedException $e) {
            return $this->redirectTo('login');
        }
    }
}
