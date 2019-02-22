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

use Illuminate\Database\Eloquent;
use Illuminate\Support\Collection;
use OpenCFP\Domain\Model\Talk;
use OpenCFP\Domain\Speaker\SpeakerProfile;

/**
 * This class is a read only version of a Talk, to be used in the views
 *
 * When initiated without an ID the rating, viewed and favorite functions will return default values
 */
class TalkProfile
{
    /**
     * @var Talk
     */
    private $talk;

    /**
     * @var int
     */
    private $userId;

    public function __construct(Talk $talk, int $userId = 0)
    {
        $this->talk   = $talk;
        $this->userId = $userId;
    }

    public function getSpeaker(): SpeakerProfile
    {
        return new SpeakerProfile($this->talk->speaker);
    }

    public function getId()
    {
        return $this->talk->id;
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
        return $this->talk->sponsor == 1;
    }

    public function isSpeakerFavorite(): bool
    {
        return $this->talk->favorite == 1;
    }

    public function isSelected(): bool
    {
        return $this->talk->selected == 1;
    }

    public function getComments(): Collection
    {
        return $this->talk->comments()->get();
    }

    public function getRating(): int
    {
        try {
            $talkMeta = $this->talk->getMetaFor($this->userId);
        } catch (Eloquent\ModelNotFoundException $exception) {
            return 0;
        }

        return (int) $talkMeta->rating;
    }

    public function isViewedByMe(): bool
    {
        try {
            $talkMeta = $this->talk->getMetaFor($this->userId);
        } catch (Eloquent\ModelNotFoundException $exception) {
            return false;
        }

        return $talkMeta->viewed == 1;
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

    public function getOtherTalks()
    {
        return $this->talk->speaker->getOtherTalks($this->talk->id);
    }
}
