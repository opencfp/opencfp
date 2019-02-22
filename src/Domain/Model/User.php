<?php

declare(strict_types=1);

/**
 * Copyright (c) 2013-2019 OpenCFP
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

/**
 * @method static self create(array $attributes)
 * @method static self|null find($id, $columns = ['*'])
 * @method static self findOrFail($id, $columns = ['*'])
 * @method static self|null first($columns = ['*'])
 * @method static Builder search(string $search = '')
 *
 * @property Collection|Talk[] $talks
 */
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

    public function persistences(): HasMany
    {
        return $this->hasMany(Persistence::class, 'user_id');
    }

    public function reminders(): HasMany
    {
        return $this->hasMany(Reminder::class);
    }

    public function throttle(): HasMany
    {
        return $this->hasMany(Throttle::class);
    }

    public static function twitterUrl(?string $handle): string
    {
        return !$handle || !\trim($handle) ? '' : ('https://twitter.com/' . $handle);
    }

    /**
     * Gets all the 'other' talks for a speaker, except the one given.
     * If called with no parameters returns all talks of that user.
     *
     * @param int $talkId
     *
     * @return Collection|Talk[]
     */
    public function getOtherTalks(int $talkId = 0): Collection
    {
        $allTalks   = $this->talks;
        $otherTalks = $allTalks->filter(function ($talk) use ($talkId) {
            if ((int) $talk['id'] == $talkId) {
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

        $this->persistences()->get()->each(function (Persistence $item) {
            if (!$item->delete()) {
                throw new \Exception('Unable to delete persistence records of user');
            }
        });

        $this->reminders()->get()->each(function (Reminder $item) {
            if (!$item->delete()) {
                throw new \Exception('Unable to delete reminder records of user');
            }
        });

        $this->throttle()->get()->each(function (Throttle $item) {
            if (!$item->delete()) {
                throw new \Exception('Unable to delete throttle records of user');
            }
        });

        if (!parent::delete()) {
            throw new \Exception('Unable to delete User');
        }

        return true;
    }
}
