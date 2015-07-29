<?php

namespace OpenCFP\Domain\Entity\Mapper;

use Spot\Mapper;

class Talk extends Mapper
{
    /**
     * Return an array of talks for use by PagerFanta where we set the
     * value in the favorite based on whether or not the current admin
     * user has favourited this talk
     *
     * @param  integer $adminUserId
     * @return array
     */
    public function getAllPagerFormatted($adminUserId, $sort)
    {
        $talks = $this->all()
            ->order($sort)
            ->with(['favorites']);
        $formatted = array();

        foreach ($talks as $talk) {
            $formatted[] = $this->createdFormattedOutput($talk, $adminUserId);
        }

        return $formatted;
    }

    /**
     * Return an array of recent talks
     *
     * @param  integer $adminUserId
     * @param  integer $limit
     * @return array
     */
    public function getRecent($adminUserId, $limit = 10)
    {
        $talks = $this->all()
            ->order(['created_at' => 'DESC'])
            ->with(['favorites', 'speaker'])
            ->limit($limit);
        $formatted = [];

        foreach ($talks as $talk) {
            $formatted[] = $this->createdFormattedOutput($talk, $adminUserId);
        }

        return $formatted;
    }

    /**
     * Return a collection of talks that a majority of the admins have liked
     *
     * @param integer $adminUserId
     * @param  integer $adminMajority
     * @return array
     */
    public function getAdminFavorites($adminUserId, $adminMajority)
    {
        $talks = $this->all()
            ->order(['created_at' => 'DESC'])
            ->with(['favorites']);

        $favorite_talks = [];

        foreach ($talks as $talk) {
            if ($talk->favorites->count() >= $adminMajority) {
                $favorite_talks[] = $talk;
            }
        }

        $formatted = [];

        foreach ($favorite_talks as $talk) {
            $formatted[] = $this->createdFormattedOutput($talk, $adminUserId);
        }

        return $formatted;
    }

    /**
     * Return a collection of entities representing talks that belong to a
     * specific user
     *
     * @param  integer $userId
     * @return array
     */
    public function getByUser($userId)
    {
        return $this->where(['user_id' => $userId]);
    }

    /**
     * Iterates over DBAL objects and returns a formatted result set
     *
     * @param  mixed   $talk
     * @param  integer $adminUserId
     * @return array
     */
    public function createdFormattedOutput($talk, $adminUserId)
    {
        if ($talk->favorites) {
            foreach ($talk->favorites as $favorite) {
                if ($favorite->admin_user_id == $adminUserId) {
                    $talk->favorite = 1;
                }
            }
        }

        $output = [
            'id' => $talk->id,
            'title' => $talk->title,
            'type' => $talk->type,
            'category' => $talk->category,
            'created_at' => $talk->created_at,
            'selected' => $talk->selected,
            'favorite' => $talk->favorite
        ];

        if ($talk->speaker) {
            $output['user'] = [
                'id' => $talk->speaker->id,
                'first_name' => $talk->speaker->first_name,
                'last_name' => $talk->speaker->last_name
            ];
        }

        return $output;
    }
}
