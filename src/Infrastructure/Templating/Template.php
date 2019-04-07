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

namespace OpenCFP\Infrastructure\Templating;

abstract class Template extends \Twig\Template
{
    /**
     * Renders a block, including global context variables, so it can be used directly
     * and still get expected results.
     *
     * @param string $name      The block name to render
     * @param array  $context   The context
     * @param array  $blocks    The current set of blocks
     * @param bool   $useBlocks Whether to use the current set of blocks
     *
     * @return string The rendered block
     */
    public function renderBlockWithContext(string $name, array $context, array $blocks = [], bool $useBlocks = true)
    {
        return $this->renderBlock($name, $this->env->mergeGlobals($context), $blocks, $useBlocks);
    }
}
