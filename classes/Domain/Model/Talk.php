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

    public function delete()
    {
        $this->deleteComments();
        $this->deleteFavorites();
        $this->deleteMeta();

        return parent::delete();
    }

    /**
     * Deletes all comments of the talk
     *
     * @throws \Exception
     */
    public function deleteComments()
    {
        $this->comments()
            ->get()
            ->each(function ($comment) {
                if (!$comment->delete()) {
                    throw new \Exception('Unable to delete all comments');
                }
            });
    }

    /**
     * Delets all favorites of the talk
     *
     * @throws \Exception
     */
    public function deleteFavorites()
    {
        $this->favorites()
            ->get()
            ->each(function ($favorite) {
                if (!$favorite->delete()) {
                    throw new \Exception('Unable to delete all favorites');
                }
            });
    }

    /**
     * Deletes all meta info of the talk
     *
     * @throws \Exception
     */
    public function deleteMeta()
    {
        $this->meta()
            ->get()
            ->each(function ($meta) {
                if (!$meta->delete()) {
                    throw new \Exception('Unable to delete all meta info');
                }
            });
    }
}
