<?php

namespace OpenCFP\Entity\Mapper;

use Spot\Mapper;

class User extends Mapper
{
    /**
     * Return an array that grabs info from the User and Speaker entities
     *
     * @param integer $user_id
     * @return array
     */
    public function getDetails($user_id)
    {
        $user = $this->where(['id' => $user_id])
            ->with(['speaker'])
            ->first();

        $details = [
            'photo_path' => null,
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'airport' => $user->airport,
            'email' => $user->email,
            'twitter' => $user->twitter,
            'bio' => null,
            'info' => null,
            'hotel' => $user->hotel,
            'transportation' => $user->transportation,
            'company' => $user->company,
            'url' => $user->url
        ];

        if (is_object($user->speaker)) {
            $details['photo_path'] = $user->speaker->photo_path;
            $details['bio'] = $user->speaker->bio;
            $details['info'] = $user->speaker->info;
        }

        return $details;
    }
}
