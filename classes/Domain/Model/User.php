<?php

namespace OpenCFP\Domain\Model;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class User extends Eloquent
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

    /**
     * Gets all the 'other' talks for a speaker, except the one given.
     * If called with no parameters returns all talks of that user.
     *
     * @param int $talkId
     *
     * @return Collection|Talk[]
     */
    public function getOtherTalks($talkId = 0): Collection
    {
        $allTalks = $this->talks;
        $otherTalks = $allTalks->filter(function ($talk) use ($talkId) {
            if ((int) $talk['id'] == (int) $talkId) {
                return false;
            }
            return true;
        });

        return $otherTalks;
    }

    public function scopeSearch(
        Builder $builder,
        $search = '',
        $orderByColumn = 'first_name',
        $orderByDirection = 'ASC'
    ) {
        if ($search == '' || $search == null) {
            return $builder->orderBy($orderByColumn, $orderByDirection);
        }
        return $builder
            ->where('first_name', 'like', '%' . $search. '%')
            ->orWhere('last_name', 'like', '%' . $search. '%')
            ->orderBy($orderByColumn, $orderByDirection);
    }
}
