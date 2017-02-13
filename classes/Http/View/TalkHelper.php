<?php

namespace OpenCFP\Http\View;

/**
 * Class TalkHelper
 * @package OpenCFP\Http\View
 */
class TalkHelper
{
    /**
     * @var array
     */
    private $categories;

    /**
     * @var array
     */
    private $levels;

    /**
     * @var array
     */
    private $types;

    /**
     * TalkHelper constructor.
     * @param $categories
     * @param $levels
     * @param $types
     */
    public function __construct($categories, $levels, $types)
    {
        $this->categories = $categories;
        $this->levels = $levels;
        $this->types = $types;
    }

    /**
     * @param $category
     * @return mixed
     */
    public function getCategoryDisplayName($category)
    {
        if (isset($this->categories[$category])) {
            return $this->categories[$category];
        }

        return $category;
    }

    /**
     * @param $type
     * @return mixed
     */
    public function getTypeDisplayName($type) {
        if (isset($this->types[$type])) {
            return $this->types[$type];
        }

        return $type;
    }

    /**
     * @param $level
     * @return mixed
     */
    public function getLevelDisplayName($level) {
        if (isset($this->levels[$level])) {
            return $this->levels[$level];
        }

        return $level;
    }
}