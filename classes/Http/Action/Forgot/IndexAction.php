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

namespace OpenCFP\Http\Action\Forgot;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Symfony\Component\Form;

final class IndexAction
{
    /**
     * @var Form\FormInterface
     */
    private $resetForm;

    public function __construct(Form\FormInterface $resetForm)
    {
        $this->resetForm = $resetForm;
    }

    /**
     * @Template("security/forgot_password.twig")
     */
    public function __invoke(): array
    {
        return [
            'form'         => $this->resetForm->createView(),
            'current_page' => 'Forgot Password',
        ];
    }
}
