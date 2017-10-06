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

    public function search($search = '', $orderBy = ['first_name' => 'ASC'])
    {
        if ($search == '' || $search == null) {
            return $this->all()->order($orderBy);
        }

        return $this->all()
            ->where(['first_name :like' => $search])
            ->orWhere(['last_name :like' => $search])
            ->order($orderBy);
    }
}
