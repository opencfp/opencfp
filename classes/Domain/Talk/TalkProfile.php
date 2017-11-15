<?php

namespace OpenCFP\Domain\Talk;

use Illuminate\Support\Collection;
use OpenCFP\Domain\Model\Talk;
use OpenCFP\Domain\Services\IdentityProvider;
use OpenCFP\Domain\Speaker\SpeakerProfile;

class TalkProfile
{
    /**
     * @var Talk
     */
    private $talk;

    private $userId;

    public function __construct(
        IdentityProvider $identityProvider
    ) {
        $this->userId = (int) $identityProvider->getCurrentUser()->id;
    }

    public function with(Talk $talk)
    {
        $this->talk = $talk;

        return $this;
    }

    public function getSpeaker(): SpeakerProfile
    {
        return new SpeakerProfile($this->talk->speaker->first());
    }

    public function getTitle()
    {
        return $this->talk->title;
    }

    public function getDescription()
    {
        return $this->talk->description;
    }

    public function getOther()
    {
        return $this->talk->other;
    }

    public function getType()
    {
        return $this->talk->type;
    }

    public function getLevel()
    {
        return $this->talk->level;
    }

    public function getCategory()
    {
        return $this->talk->category;
    }

    public function getSlides()
    {
        return $this->talk->slides;
    }

    public function isDesired(): bool
    {
        return $this->talk->desired == 1;
    }

    public function isSponsor(): bool
    {
        return $this->talk->sponsor ==1;
    }

    public function isSpeakerFavorite(): bool
    {
        return $this->talk->favorite ==1;
    }

    public function isSelected(): bool
    {
        return $this->talk->selected ==1;
    }

    public function getComments(): Collection
    {
        return $this->talk->comments()->get();
    }

    public function getRating(): int
    {
        try {
            return (int) ($this->talk
                ->getMetaFor($this->userId)
                ->rating);
        } catch (\Exception $e) {
            return 0;
        }
    }

    public function isViewed(): bool
    {
        try {
            return $this->talk
                    ->getMetaFor($this->userId)
                    ->viewed == 1;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function isMyFavorite(): bool
    {
        try {
            return $this->talk->favorites()->get()->contains(function ($value) {
                return $value->admin_user_id == $this->userId;
            });
        } catch (\Exception $e) {
            return false;
        }
    }
}
