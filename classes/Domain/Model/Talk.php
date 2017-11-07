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
     * @param int $limit maximum ammount of entries to return.
     */
    public function scopeRecent(Builder $query, int $limit = 10): Builder
    {
        return $query
            ->orderBy('created_at')
            ->with(['favorites', 'meta'])
            ->take($limit);
    }
}
