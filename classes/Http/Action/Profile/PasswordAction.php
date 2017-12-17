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

namespace OpenCFP\Http\Action\Profile;

use Symfony\Component\HttpFoundation;
use Twig_Environment;

final class PasswordAction
{
    /**
     * @var Twig_Environment
     */
    private $twig;

    public function __construct(Twig_Environment $twig)
    {
        $this->twig = $twig;
    }

    public function __invoke(): HttpFoundation\Response
    {
        $content = $this->twig->render('user/change_password.twig');

        return new HttpFoundation\Response($content);
    }
}
