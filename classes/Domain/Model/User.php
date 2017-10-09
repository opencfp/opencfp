<?php

namespace OpenCFP\Domain\Model;

use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    public function talks()
    {
        return $this->hasMany(Talk::class);
    }

    public function groups()
    {
        return $this->belongsToMany(Group::class, 'users_groups', 'user_id', 'group_id');
    }

    public function comments()
    {
        return $this->hasMany(TalkComment::class);
    }

    public function meta()
    {
        return $this->hasMany(TalkMeta::class, 'admin_user_id');
    }
}
