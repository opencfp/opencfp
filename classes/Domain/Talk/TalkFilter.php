<?php

namespace OpenCFP\Domain\Talk;

use OpenCFP\Domain\Model\Talk;

class TalkFilter
{
    /**
     * Column Sort By White List
     *
     * @var array
     */
    protected $order_by_whitelist = [
        'created_at',
        'title',
        'type',
        'category',
    ];

    /**
     * @var Talk
     */
    private $talk;
    /**
     * @var TalkFormatter
     */
    private $formatter;

    public function __construct(TalkFormatter $talkFormatter, Talk $talk)
    {
        $this->formatter = $talkFormatter;
        $this->talk      = $talk;
    }

    public function getTalks($admin_user_id, $filter= null, $options = []): array
    {
        // Merge options with default options
        $options = $this->getSortOptions(
            $options,
            [
                'order_by' => 'created_at',
                'sort'     => 'ASC',
            ]
        );

        $talks = $this->getFilteredTalks($admin_user_id, $filter)
            ->orderBy($options['order_by'], $options['sort'])->get();

        return $this->formatter->formatList($talks, $admin_user_id)->toArray();
    }

    public function getFilteredTalks($admin_user_id, $filter = null)
    {
        if ($filter === null) {
            return $this->talk;
        }
        switch (strtolower($filter)) {
            case 'selected':
                return $this->talk->selected();

            case 'notviewed':
                return $this->talk->notViewedBy($admin_user_id);

            case 'notrated':
                return $this->talk->notRatedBy($admin_user_id);

            case 'toprated':
                return $this->talk->topRated();

            case 'plusone':
                return $this->talk->ratedPlusOneBy($admin_user_id);

            case 'viewed':
                return $this->talk->viewedBy($admin_user_id);

            case 'favorited':
                return $this->talk->favoritedBy($admin_user_id);

            default:
                return $this->talk;
        }
    }

    /**
     * Get sorting options, order_by and sort direction
     *
     * @param array $options        Sorting Options to Apply
     * @param array $defaultOptions Default Sorting Options
     *
     * @return array
     */
    protected function getSortOptions(array $options, array $defaultOptions)
    {
        if (!isset($options['order_by']) || !in_array($options['order_by'], $this->order_by_whitelist)) {
            $options['order_by'] = $defaultOptions['order_by'];
        }

        if (!isset($options['sort']) || !in_array($options['sort'], ['ASC', 'DESC'])) {
            $options['sort'] = $defaultOptions['sort'];
        }

        return array_merge($defaultOptions, $options);
    }
}
