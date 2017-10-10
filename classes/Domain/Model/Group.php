<?php

namespace OpenCFP\Domain\Model;

class Group extends Eloquent
{
    protected $table = 'groups';

    public function users()
    {
        return $this->belongsToMany(User::class, 'users_groups', 'group_id', 'user_id');
    }
}
