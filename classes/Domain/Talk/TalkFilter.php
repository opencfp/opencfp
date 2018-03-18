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

namespace OpenCFP\Domain\Talk;

use OpenCFP\Domain\Model\Talk;

class TalkFilter
{
    /**
     * Column Sort By White List
     *
     * @var array
     */
    private $orderByWhiteList = [
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

    public function getTalks(int $adminUserId, $filter = null, $category = null, $type = null, $options = []): array
    {
        // Merge options with default options
        $options = $this->getSortOptions(
            $options,
            [
                'order_by' => 'created_at',
                'sort'     => 'ASC',
            ]
        );

        $talks = $this->getFilteredTalks($adminUserId, $filter, $category, $type)
            ->orderBy($options['order_by'], $options['sort'])->get();

        return $this->formatter->formatList($talks, $adminUserId)->toArray();
    }

    public function getFilteredTalks(int $adminUserId, $filter = null, $category = null, $type = null)
    {
        $talk = $this->talk;

        if (!empty($filter)) {
            $new_talk = $talk;
            switch (\strtolower($filter)) {
                case 'selected':
                    $new_talk = $talk->selected();
                break;

                case 'notviewed':
                    $new_talk = $talk->notViewedBy($adminUserId);
                break;

                case 'notrated':
                    $new_talk = $talk->notRatedBy($adminUserId);
                break;

                case 'toprated':
                    $new_talk = $talk->topRated();
                break;

                case 'plusone':
                    $new_talk = $talk->ratedPlusOneBy($adminUserId);
                break;

                case 'viewed':
                    $new_talk = $talk->viewedBy($adminUserId);
                break;

                case 'favorited':
                    $new_talk = $talk->favoritedBy($adminUserId);
                break;
            }
            $talk = $new_talk;
        }

        if (!empty($category)) {
            $new_talk = $talk->category($category);
            $talk = $new_talk;
        }

        if (!empty($type)) {
            $new_talk = $talk->type($type);
            $talk = $new_talk;
        }

        return $talk;
    }

    /**
     * Get sorting options, order_by and sort direction
     *
     * @param array $options        Sorting Options to Apply
     * @param array $defaultOptions Default Sorting Options
     *
     * @return array
     */
    private function getSortOptions(array $options, array $defaultOptions)
    {
        if (!isset($options['order_by']) || !\in_array($options['order_by'], $this->orderByWhiteList)) {
            $options['order_by'] = $defaultOptions['order_by'];
        }

        if (!isset($options['sort']) || !\in_array($options['sort'], ['ASC', 'DESC'])) {
            $options['sort'] = $defaultOptions['sort'];
        }

        return \array_merge($defaultOptions, $options);
    }
}
