<?php

namespace OpenCFP\Domain\Services;

use OpenCFP\Domain\Entity\Talk as TalkEntity;
use OpenCFP\Domain\Model\Talk as TalkModel;
use OpenCFP\Domain\Model\TalkMeta;

class TalkFormatter
{

    /**
     * Grabs the related meta information of the talk, or 0's when there is none
     *
     * @param TalkModel|TalkEntity $talk Talk that we want the meta information off
     * @param int $admin_user_id user di
     * @return array|TalkMeta
     */
    protected function getTalkMeta($talk, $admin_user_id)
    {
        $meta = TalkMeta::where('talk_id', $talk->id)->where('admin_user_id', $admin_user_id)->first();

        if ($meta instanceof TalkMeta) {
            return $meta;
        }

        return ['rating' => 0, 'viewed' => 0];
    }

    /**
     * Iterates over DBAL objects and returns a formatted result set
     *
     * @param  mixed $talk
     * @param  integer $admin_user_id
     * @param bool $userData grab the speaker data or not
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
        $meta = $this->getTalkMeta($talk, $admin_user_id);

        $output = [
            'id' => $talk->id,
            'title' => $talk->title,
            'type' => $talk->type,
            'category' => $talk->category,
            'created_at' => $talk->created_at,
            'selected' => $talk->selected,
            'favorite' => $talk->favorite,
            'meta' => $meta,
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
}
