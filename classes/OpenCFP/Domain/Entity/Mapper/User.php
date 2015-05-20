<?php

namespace OpenCFP\Domain\Entity\Mapper;

use Spot\Mapper;

class User extends Mapper
{
    /**
     * Return an array that grabs info from the User and Speaker entities
     *
     * @param  integer $user_id
     * @return array
     */
    public function getDetails($user_id)
    {
        $user = $this->where(['id' => $user_id])
            ->first();

        return $user;
    }

    public function getByEmail($email)
    {
        $response = $this->where(['email' => $email])->first();

        return $response;

    }
}
