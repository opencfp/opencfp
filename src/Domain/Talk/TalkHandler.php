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

namespace OpenCFP\Domain\Talk;

use OpenCFP\Domain\Model\Favorite;
use OpenCFP\Domain\Model\Talk;
use OpenCFP\Domain\Model\TalkComment;
use OpenCFP\Domain\Services\Authentication;
use OpenCFP\Domain\Services\TalkRating\TalkRatingException;
use OpenCFP\Domain\Services\TalkRating\TalkRatingStrategy;

class TalkHandler
{
    /**
     * @var Talk
     */
    private $talk;

    /**
     * @var int
     */
    private $userId;

    /**
     * @var TalkRatingStrategy
     */
    private $ratingStrategy;

    public function __construct(
        Authentication $authentication,
        TalkRatingStrategy $ratingStrategy
    ) {
        $this->userId         = $authentication->user()->getId();
        $this->ratingStrategy = $ratingStrategy;
    }

    /**
     * This function is used to dynamically set the Talk to deal with, after the class is initialized by its provider.
     *
     * @param Talk $talk
     *
     * @return $this
     */
    public function with(Talk $talk): self
    {
        $this->talk = $talk;

        return $this;
    }

    /**
     * Sets the Talk according to the ID
     *
     * @param int $talkId
     *
     * @return $this
     */
    public function grabTalk(int $talkId): self
    {
        $this->talk = Talk::find($talkId);

        return $this;
    }

    public function hasTalk(): bool
    {
        return $this->talk instanceof Talk;
    }

    public function commentOn(string $message): bool
    {
        if ($this->hasTalk()) {
            TalkComment::create([
                'user_id' => $this->userId,
                'talk_id' => $this->talk->id,
                'message' => $message,
            ]);

            return true;
        }

        return false;
    }

    public function select(bool $selected = true): bool
    {
        if ($this->hasTalk()) {
            $this->talk->selected = $selected ? 1 : 0;

            return $this->talk->save();
        }

        return false;
    }

    /**
     * Creates or deletes a favorite of the current user
     *
     * @param bool $create will create a favorite on true, and delete it on false
     *
     * @return bool
     */
    public function setFavorite(bool $create = true): bool
    {
        if ($this->hasTalk()) {
            return $create ? $this->createFavoriteForCurrentUser() : $this->clearFavoriteOfCurrentUser();
        }

        return false;
    }

    private function createFavoriteForCurrentUser(): bool
    {
        $this->talk
            ->favorites()
            ->firstOrCreate([
                'admin_user_id' => $this->userId,
                'talk_id'       => $this->talk->id,
            ]);

        return true;
    }

    private function clearFavoriteOfCurrentUser(): bool
    {
        try {
            $favorite = $this->talk->favorites()
                ->where('talk_id', $this->talk->id)
                ->where('admin_user_id', $this->userId)
                ->first();

            if ($favorite instanceof Favorite) {
                $favorite->delete();
            }

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function rate($rating): bool
    {
        if ($this->hasTalk()) {
            try {
                $this->ratingStrategy->rate($this->talk->id, $rating);

                return true;
            } catch (TalkRatingException $e) {
                return false;
            }
        }

        return false;
    }

    public function view(): bool
    {
        if ($this->hasTalk()) {
            try {
                $meta = $this->talk
                    ->getMetaFor($this->userId, true);
                $meta->viewed = 1;

                return $meta->save();
            } catch (\Exception $e) {
                return false;
            }
        }

        return false;
    }

    public function getProfile()
    {
        return new TalkProfile($this->talk, $this->userId);
    }
}
