<?php

namespace OpenCFP\Domain\Talk;

use OpenCFP\Domain\Model\Favorite;
use OpenCFP\Domain\Model\Talk;
use OpenCFP\Domain\Model\TalkComment;
use OpenCFP\Domain\Services\IdentityProvider;
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
        IdentityProvider $identityProvider,
        TalkRatingStrategy $ratingStrategy
    ) {
        $this->userId         = (int) $identityProvider->getCurrentUser()->id;
        $this->ratingStrategy = $ratingStrategy;
    }

    /**
     * This function is used to dynamically set the Talk to deal with, after the class is initialized by its provider.
     *
     * @param Talk $talk
     *
     * @return $this
     */
    public function with(Talk $talk)
    {
        $this->talk = $talk;

        return $this;
    }

    public function commentOn(string $message): bool
    {
        TalkComment::create([
           'user_id'  => $this->userId,
            'talk_id' => $this->talk->id,
            'message' => $message,
        ]);

        return true;
    }

    public function select(bool $selected = true): bool
    {
        $this->talk->selected = $selected;

        return $this->talk->save();
    }

    public function favorite(bool $create = true): bool
    {
        return $create ? $this->createFavorite() : $this->deleteFavorite();
    }

    private function createFavorite(): bool
    {
        Favorite::firstOrCreate([
            'user_id' => $this->userId,
            'talk_id' => $this->talk->id,
        ]);

        return true;
    }

    private function deleteFavorite(): bool
    {
        try {
            Favorite::findOrFail([
                'user_id' => $this->userId,
                'talk_id' => $this->talk->id,
            ])->delete();

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function rate($rating): bool
    {
        try {
            $this->ratingStrategy->rate($this->talk->id, $rating);

            return true;
        } catch (TalkRatingException $e) {
            return false;
        }
    }

    public function view(): bool
    {
        try {
            $meta = $this->talk
                ->getMetaFor($this->userId, true);
            $meta->viewed = 1;

            return $meta->save();
        } catch (\Exception $e) {
            return false;
        }
    }
}
