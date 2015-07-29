<?php

namespace OpenCFP\Domain\Entity\Mapper;

use Spot\Mapper;

class User extends Mapper
{
    /**
     * Return an array that grabs info from the User and Speaker entities
     *
     * @param  integer $userId
     * @return array
     */
    public function getDetails($userId)
    {
        $user = $this->where(['id' => $userId])
            ->first();

        return $user;
    }
}
