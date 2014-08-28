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

        return [
            'photo_path' => $user->speaker->photo_path,
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'airport' => $user->airport,
            'email' => $user->email,
            'twitter' => $user->twitter,
            'bio' => $user->speaker->bio,
            'info' => $user->speaker->info,
            'hotel' => $user->hotel,
            'transportation' => $user->transportation
        ];
    }
}
