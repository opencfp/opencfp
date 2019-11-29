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

namespace OpenCFP\Http\Action\Signup;

use Symfony\Component\HttpFoundation;
use Twig\Environment;

final class PrivacyAction
{
    /**
     * @var Twig\Environment
     */
    private $twig;

    public function __construct(Environment $twig)
    {
        $this->twig = $twig;
    }

    public function __invoke(HttpFoundation\Request $request): HttpFoundation\Response
    {
        $content = $this->twig->render('user/privacy.twig');

        return new HttpFoundation\Response($content);
    }
}
