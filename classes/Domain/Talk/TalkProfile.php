<?php

namespace OpenCFP\Domain\Talk;

use OpenCFP\Domain\Model\Talk;
use OpenCFP\Domain\Speaker\SpeakerProfile;

class TalkProfile
{
    /**
     * @var Talk
     */
    private $talk;

    private $userId;

    public function __construct($talk, int $userId)
    {
        if ($talk instanceof Talk) {
            $this->talk = $talk;
        }
        if (is_int($talk)) {
            $this->talk   = Talk::findOrFail($talk);
        }
        $this->userId = $userId;
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

    public function isFavorite(): bool
    {
        return $this->talk->favorite ==1;
    }

    public function isSelected(): bool
    {
        return $this->talk->selected ==1;
    }

    public function getComments()
    {
        return $this->talk->comments()->get();
    }

    public function getRating()
    {
        return $this->talk
            ->meta()
            ->where('admin_user_id', $this->userId)
            ->first()
            ->rating;
    }

    public function isViewed(): bool
    {
        return $this->talk
            ->meta()
            ->where('admin_user_id', $this->userId)
            ->first()
            ->viewed == 1;
    }
}
