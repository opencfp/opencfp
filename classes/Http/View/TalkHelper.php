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

namespace OpenCFP\Http\View;

class TalkHelper
{
    /**
     * @var string[]
     */
    private $categories;

    /**
     * @var string[]
     */
    private $levels;

    /**
     * @var string[]
     */
    private $types;

    /**
     * @param string[] $categories
     * @param string[] $levels
     * @param string[] $types
     */
    public function __construct(array $categories, array $levels, array $types)
    {
        $this->categories = $categories;
        $this->levels     = $levels;
        $this->types      = $types;
    }
    
    /**
     * @return string[]
     */
    public function getTalkCategories(): array
    {
        return $this->categories;
    }

    public function getCategoryDisplayName(string $category): string
    {
        if (isset($this->categories[$category])) {
            return $this->categories[$category];
        }

        return $category;
    }

    /**
     * @return string[]
     */
    public function getTalkTypes(): array
    {
        return $this->types;
    }

    public function getTypeDisplayName(string $type): string
    {
        if (isset($this->types[$type])) {
            return $this->types[$type];
        }

        return $type;
    }

    /**
     * @return string[]
     */
    public function getTalkLevels(): array
    {
        return $this->levels;
    }

    public function getLevelDisplayName(string $level): string
    {
        if (isset($this->levels[$level])) {
            return $this->levels[$level];
        }

        return $level;
    }
}
