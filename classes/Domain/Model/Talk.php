<?php

namespace OpenCFP\Domain\Model;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @method Builder recent(int $limit=10)
 * @method Builder selected()
 * @method Builder notViewedBy(int $userId)
 * @method Builder notRatedBy(int $userId)
 * @method Builder topRated()
 * @method Builder ratedPlusOneBy(int $userId)
 * @method Builder viewedBy(int $userId)
 * @method Builder favoritedBy(int $userId)
 */
class Talk extends Eloquent
{
    public function speaker(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function favorites(): HasMany
    {
        return $this->hasMany(Favorite::class, 'talk_id');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(TalkComment::class);
    }

    public function meta(): HasMany
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

    public function scopeSelected(Builder $query): Builder
    {
        return $query
            ->where('selected', 1);
    }

    public function scopeViewedBy(Builder $query, int $userId): Builder
    {
        return $query
            ->whereHas('meta', function (Builder $query) use ($userId) {
                $query
                    ->where('admin_user_id', $userId)
                    ->where('viewed', 1);
            });
    }

    public function scopeFavoritedBy(Builder $query, int $userId): Builder
    {
        return $query
            ->whereHas('favorites', function (Builder $query) use ($userId) {
                $query->where('admin_user_id', $userId);
            });
    }

    public function scopeRatedPlusOneBy(Builder $query, int $userId): Builder
    {
        return $query
            ->whereHas('meta', function (Builder $query) use ($userId) {
                $query
                   ->where('admin_user_id', $userId)
                   ->where('rating', 1);
            });
    }

    public function scopeNotRatedBy(Builder $query, int $userId): Builder
    {
        return $query
            ->whereDoesntHave('meta', function (Builder $query) use ($userId) {
                $query
                    ->where('admin_user_id', $userId)
                    ->where('rating', '!=', 0);
            });
    }

    public function scopeNotViewedBy(Builder $query, int $userId): Builder
    {
        return $query
            ->whereDoesntHave('meta', function (Builder $query) use ($userId) {
                $query
                    ->where('admin_user_id', $userId)
                    ->where('viewed', '!=', 0);
            });
    }

    public function scopeTopRated(Builder $query): Builder
    {
        return $query->selectRaw('talks.*, sum(m.rating) as total')
            ->join('talk_meta as m', 'talks.id', '=', 'm.talk_id')
            ->groupBy('m.talk_id')
            ->havingRaw('total > 0')
            ->orderBy('total', 'desc');
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

    /**
     * Gets the meta object of the current talk, with a specific Admin.
     *
     * @param int  $userId
     * @param bool $willCreate on true it will create a new model if it doesn't exists, on false
     *                         it will throw an error.
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     *
     * @return \Illuminate\Database\Eloquent\Model|static
     */
    public function getMetaFor(int $userId, bool $willCreate = false)
    {
        return $willCreate ? $this->getOrCreateMeta($userId) : $this->getOrFailMeta($userId);
    }

    private function getOrCreateMeta(int $userId)
    {
        return $this->meta()->firstOrCreate([
            'admin_user_id' => $userId,
            'talk_id'       => $this->id,
        ]);
    }

    private function getOrFailMeta(int $userId)
    {
        return $this->meta()
            ->where('admin_user_id', $userId)
            ->where('talk_id', $this->id)
            ->firstOrFail();
    }
}
