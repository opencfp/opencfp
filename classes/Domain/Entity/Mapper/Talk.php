<?php

namespace OpenCFP\Domain\Entity\Mapper;

use Spot\Mapper;

class Talk extends Mapper
{
    /**
     * Column Sort By White List
     * @var array
     */
    protected $order_by_whitelist = [
        'created_at',
        'title',
        'type',
        'category',
    ];

    /**
     * Return an array of talks for use by PagerFanta where we set the
     * value in the favorite based on whether or not the current admin
     * user has favourited this talk
     *
     * @param integer $admin_user_id
     * @param array $options
     * @return array If order by is not in white list
     * @internal param string $order_by
     * @internal param string $sort Sort Direction
     */
    public function getAllPagerFormatted($admin_user_id, $options, $userData = true, $where = null)
    {
        // Merge options with default options
        $options = $this->getSortOptions(
            $options,
            [
                'order_by' => 'created_at',
                'sort' => 'ASC',
            ]
        );

        $formatted = [];

        if ($where) {
            $talks = $this->all()
                ->order([$options['order_by'] => $options['sort']])
                ->where($where);
        } else {
            $talks = $this->all()
                ->order([$options['order_by'] => $options['sort']])
                ->with(['favorites']);
        }

        foreach ($talks as $talk) {
            $formatted[] = $this->createdFormattedOutput($talk, $admin_user_id, $userData);
        }

        return $formatted;
    }

    /**
     * Return a collection of talks that have been selected
     *
     * @param integer $admin_user_id
     * @param array $options Ordery By and Sorting Options
     * @return array
     */
    public function getSelected($admin_user_id, $options = [])
    {
        // Merge options with default options
        $options = $this->getSortOptions(
            $options,
            [
                'order_by' => 'created_at',
                'sort' => 'DESC',
            ]
        );

        $talks = $this->all()
            ->where(['selected' => 1])
            ->order([$options['order_by'] => $options['sort']]);

        $formatted = [];
        foreach ($talks as $talk) {
            $formatted[] = $this->createdFormattedOutput($talk, $admin_user_id);
        }

        return $formatted;
    }

    /**
     * Return an array of recent talks
     *
     * @param  integer $admin_user_id
     * @param int $limit
     * @return array
     * @internal param int $limt
     */
    public function getRecent($admin_user_id, $limit = 10)
    {
        $talks = $this->all()
            ->order(['created_at' => 'DESC'])
            ->with(['favorites'])
            ->limit($limit);
        $formatted = [];

        foreach ($talks as $talk) {
            $formatted[] = $this->createdFormattedOutput($talk, $admin_user_id);
        }

        return $formatted;
    }

    /**
     * Return a collection of talks that a majority of the admins have liked
     *
     * @param integer $admin_user_id
     * @param array $options Ordery By and Sorting Options
     * @return array
     */
    public function getFavoritesByUserId($admin_user_id, $options = [])
    {
        // Merge options with default options
        $options = $this->getSortOptions(
            $options,
            [
                'order_by' => 'f.created',
                'sort' => 'DESC',
            ]
        );

        $talks = $this->query(
            "SELECT t.* FROM talks t "
            . "LEFT JOIN favorites f ON t.id = f.talk_id "
            . "WHERE f.admin_user_id = :user_id "
            . "ORDER BY {$options['order_by']} {$options['sort']}",
            ['user_id' => $admin_user_id]
        );

        $formatted = [];
        foreach ($talks as $talk) {
            $formatted[] = $this->createdFormattedOutput($talk, $admin_user_id);
        }

        return $formatted;
    }

    /**
     * Return a collection of top rated talks ordered by rating
     * the talk rating must be above 0 to show on list
     *
     * @param integer $admin_user_id
     * @param array $options Ordery By and Sorting Options
     * @return array
     */
    public function getTopRatedByUserId($admin_user_id, $options = [])
    {
        // Merge options with default options
        $options = $this->getSortOptions(
            $options,
            [
                'order_by' => 'total_rating',
                'sort' => 'DESC',
            ]
        );

        $talks = $this->query(
            "SELECT t.*, SUM(m.rating) AS total_rating, COUNT(m.rating) as review_count FROM talks t "
            . "LEFT JOIN talk_meta m ON t.id = m.talk_id "
            . "GROUP BY m.`talk_id` "
            . "HAVING total_rating > 0 "
            . "ORDER BY {$options['order_by']} {$options['sort']}",
            ['user_id' => $admin_user_id]
        );

        $formatted = [];
        foreach ($talks as $talk) {
            $formatted[] = $this->createdFormattedOutput($talk, $admin_user_id);
        }

        return $formatted;
    }

    /**
     * Return a collection of talks not viewed by admin
     *
     * @param integer $admin_user_id
     * @param array $options Ordery By and Sorting Options
     * @return array
     */
    public function getNotViewedByUserId($admin_user_id, $options = [])
    {
        // Merge options with default options
        $options = $this->getSortOptions(
            $options,
            [
                'order_by' => 't.created_at',
                'sort' => 'DESC',
            ]
        );

        $talks = $this->query(
            "SELECT t.* FROM talks t "
            . "LEFT JOIN talk_meta m ON t.id = m.talk_id "
            . "WHERE (m.viewed = 0 AND m.admin_user_id = :user_id) OR m.viewed IS NULL "
            . "ORDER BY {$options['order_by']} {$options['sort']}",
            ['user_id' => $admin_user_id]
        );

        $formatted = [];
        foreach ($talks as $talk) {
            $formatted[] = $this->createdFormattedOutput($talk, $admin_user_id);
        }

        return $formatted;
    }

    /**
     * Return a collection of talks viewed by admin
     *
     * @param integer $admin_user_id
     * @param array $options Ordery By and Sorting Options
     * @return array
     */
    public function getViewedByUserId($admin_user_id, $options = [])
    {
        // Merge options with default options
        $options = $this->getSortOptions(
            $options,
            [
                'order_by' => 't.created_at',
                'sort' => 'DESC',
            ]
        );

        $talks = $this->query(
            "SELECT t.* FROM talks t "
            . "RIGHT JOIN talk_meta m ON t.id = m.talk_id "
            . "WHERE m.admin_user_id = :user_id AND m.viewed = 1 "
            . "ORDER BY {$options['order_by']} {$options['sort']}",
            ['user_id' => $admin_user_id]
        );

        $formatted = [];
        foreach ($talks as $talk) {
            $formatted[] = $this->createdFormattedOutput($talk, $admin_user_id);
        }

        return $formatted;
    }

    /**
     * Return a collection of talks rated by admin
     *
     * @param integer $admin_user_id
     * @param array $options Ordery By and Sorting Options
     * @return array
     */
    public function getRatedByUserId($admin_user_id, $options = [])
    {
        // Merge options with default options
        $options = $this->getSortOptions(
            $options,
            [
                'order_by' => 'm.created',
                'sort' => 'DESC',
            ]
        );

        $talks = $this->query(
            "SELECT t.* FROM talks t "
            . "RIGHT JOIN talk_meta m ON t.id = m.talk_id "
            . "WHERE m.admin_user_id = :user_id AND (m.rating = 1 OR m.rating = -1) "
            . "ORDER BY {$options['order_by']} {$options['sort']}",
            ['user_id' => $admin_user_id]
        );

        $formatted = [];
        foreach ($talks as $talk) {
            $formatted[] = $this->createdFormattedOutput($talk, $admin_user_id);
        }

        return $formatted;
    }

    /**
     * Return a collection of talks not rated by admin
     *
     * @param integer $admin_user_id
     * @param array $options Ordery By and Sorting Options
     * @return array
     */
    public function getNotRatedByUserId($admin_user_id, $options = [])
    {
        // Merge options with default options
        $options = $this->getSortOptions(
            $options,
            [
                'order_by' => 't.created_at',
                'sort' => 'DESC',
            ]
        );

        $talks = $this->query(
            "SELECT t.* FROM talks t "
            . "LEFT JOIN talk_meta m ON (t.id = m.talk_id AND m.admin_user_id = :user_id)"
            . "WHERE m.rating = 0 OR m.rating IS NULL "
            . "ORDER BY {$options['order_by']} {$options['sort']}",
            ['user_id' => $admin_user_id]
        );

        $formatted = [];
        foreach ($talks as $talk) {
            $formatted[] = $this->createdFormattedOutput($talk, $admin_user_id);
        }

        return $formatted;
    }

    /**
     * Get a collection of talks filtered by a specific column
     *
     * @param string $column Column to filter results with
     * @param mixed $value Column value
     * @param integer $admin_user_id
     * @param array $options Ordery By and Sorting Options
     * @return array column is not in the column white list
     * @throws InvalidArgumentException
     */
    public function getTalksFilteredBy($column, $value, $admin_user_id, $options = [])
    {
        // Merge options with default options
        $options = array_merge(
            $options,
            [
                'order_by' => 'created_at',
                'sort' => 'DESC',
            ]
        );

        $column_white_list = [
            'category',
            'type',
            'level',
        ];

        if (!in_array($column, $column_white_list)) {
            throw new InvalidArgumentException('Invalid Order By Column ' . $options['order_by']);
        }

        $talks = $this->all()
            ->with(['favorites', 'meta'])
            ->where([$column => $value])
            ->order([$options['order_by'] => $options['sort']]);

        $formatted = [];
        foreach ($talks as $talk) {
            $formatted[] = $this->createdFormattedOutput($talk, $admin_user_id);
        }

        return $formatted;
    }

    /**
     * Return a collection of entities representing talks that belong to a
     * specific user
     *
     * @param  integer $user_id
     * @return array
     */
    public function getByUser($user_id)
    {
        return $this->where(['user_id' => $user_id]);
    }

    /**
     * Iterates over DBAL objects and returns a formatted result set
     *
     * @param  mixed   $talk
     * @param  integer $admin_user_id
     * @return array
     */
    public function createdFormattedOutput($talk, $admin_user_id, $userData = true)
    {
        if ($talk->favorites) {
            foreach ($talk->favorites as $favorite) {
                if ($favorite->admin_user_id == $admin_user_id) {
                    $talk->favorite = 1;
                }
            }
        }

        $mapper = $this->getMapper(\OpenCFP\Domain\Entity\TalkMeta::class);
        $talk_meta = $mapper->where(['talk_id' => $talk->id, 'admin_user_id' => $admin_user_id])
            ->first();

        $output = [
            'id' => $talk->id,
            'title' => $talk->title,
            'type' => $talk->type,
            'category' => $talk->category,
            'created_at' => $talk->created_at,
            'selected' => $talk->selected,
            'favorite' => $talk->favorite,
            'meta' => ($talk_meta) ?: $mapper->get(),
            'description' => $talk->description,
            'slides' => $talk->slides,
            'other' => $talk->other,
            'level' => $talk->level,
            'desired' => $talk->desired,
            'sponsor' => $talk->sponsor,
        ];

        if ($talk->speaker && $userData) {
            $output['user'] = [
                'id' => $talk->speaker->id,
                'first_name' => $talk->speaker->first_name,
                'last_name' => $talk->speaker->last_name,
            ];

            $output += [
                'speaker_id' => $talk->speaker->id,
                'speaker_first_name' => $talk->speaker->first_name,
                'speaker_last_name' => $talk->speaker->last_name,
                'speaker_email' => $talk->speaker->email,
                'speaker_company' => $talk->speaker->company,
                'speaker_twitter' => $talk->speaker->twitter,
                'speaker_airport' => $talk->speaker->airport,
                'speaker_hotel' => $talk->speaker->hotel,
                'speaker_transportation' => $talk->speaker->transportation,
                'speaker_info' => $talk->speaker->info,
                'speaker_bio' => $talk->speaker->bio,
            ];
        }

        if ($talk->total_rating) {
            $output['total_rating'] = $talk->total_rating;
            $output['review_count'] = $talk->review_count;
        }

        return $output;
    }

    /**
     * Get sorting options, order_by and sort direction
     *
     * @param array $options Sorting Options to Apply
     * @param array $defaultOptions Default Sorting Options
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
