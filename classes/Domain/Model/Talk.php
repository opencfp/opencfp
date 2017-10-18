<?php

namespace OpenCFP\Domain\Model;

use Illuminate\Database\Eloquent\Builder;

class Talk extends Eloquent
{
    public function speaker()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function favorites()
    {
        return $this->hasMany(Favorite::class, 'talk_id');
    }

    public function comments()
    {
        return $this->hasMany(TalkComment::class);
    }

    public function meta()
    {
        return $this->hasMany(TalkMeta::class, 'talk_id');
    }

    /**
     * Returns the most recent talks
     *
     * @param Builder $query
     * @param int $limit
     * @return \Illuminate\Database\Query\Builder|static
     */
    public function scopeRecent(Builder $query, $limit = 10)
    {
        return $query
            ->orderBy('created_at')
            ->with(['favorites', 'meta'])
            ->take($limit);
    }
}
