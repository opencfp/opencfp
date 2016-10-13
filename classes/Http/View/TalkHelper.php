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
     * TalkHelper constructor.
     * @param $categories
     */
    public function __construct($categories)
    {
        $this->categories = $categories;
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
}
