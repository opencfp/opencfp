<?php

/**
 * Copyright (c) 2013-2017 OpenCFP
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @see https://github.com/opencfp/opencfp
 */

namespace OpenCFP\Domain\Model;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

class User extends Eloquent
{
    public function talks(): HasMany
    {
        return $this->hasMany(Talk::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(TalkComment::class);
    }

    public function meta(): HasMany
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
        $allTalks   = $this->talks;
        $otherTalks = $allTalks->filter(function ($talk) use ($talkId) {
            if ((int) $talk['id'] == (int) $talkId) {
                return false;
            }

            return true;
        });

        return $otherTalks;
    }

    /**
     * Will preform a like search with given search string or first or last name.
     *
     * @param Builder     $builder
     * @param null|string $search           Name to search for
     * @param string      $orderByColumn
     * @param string      $orderByDirection
     *
     * @return Builder
     */
    public function scopeSearch(
        Builder $builder,
        $search = '',
        string $orderByColumn = 'first_name',
        string $orderByDirection = 'ASC'
    ): Builder {
        if ($search == '' || $search == null) {
            return $builder->orderBy($orderByColumn, $orderByDirection);
        }

        return $builder
            ->where('first_name', 'like', '%' . $search . '%')
            ->orWhere('last_name', 'like', '%' . $search . '%')
            ->orderBy($orderByColumn, $orderByDirection);
    }

    /**
     * Deletes user, all of their talks, and meta/favorites/comments
     *
     * @throws \Exception
     *
     * @return bool
     */
    public function delete(): bool
    {
        $this->talks()
            ->get()
            ->each(function ($talk) {
                if (!$talk->delete()) {
                    throw new \Exception('Unable to delete talks of user');
                }
            });
        if (!parent::delete()) {
            throw new \Exception('Unable to delete User');
        }

        return true;
    }
}
