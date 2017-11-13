<?php

namespace OpenCFP\Domain\Speaker;

use OpenCFP\Domain\Model\Talk;
use OpenCFP\Domain\Model\User;

/**
 * This is a user-facing read-only projection of a Speaker and their Talks forming
 * a combined "profile". It is to be used to aide in rendering of views facing the speaker themselves.
 * It is a speaker's individual profile.
 */
class SpeakerProfile
{
    /**
     * @var User
     */
    protected $speaker;

    public function __construct($speaker)
    {
        $this->speaker = $speaker;
    }

    public function needsProfile(): bool
    {
        return $this->speaker->has_made_profile == 0;
    }

    /**
     * Returns a collection of the speaker's talks.
     *
     * This method should be watched for abuse. It is reasonable that a
     * better implementation would be to return an array of hashes rather than
     * a Spot entity. As long as views don't go wild, it'll be okay.
     *
     * This docblock is one of those unnecessary apologetic comments...
     *
     * @return Talk[]
     */
    public function getTalks()
    {
        return $this->speaker->talks;
    }

    public function getName(): string
    {
        return $this->speaker->first_name . ' ' . $this->speaker->last_name;
    }

    public function getEmail()
    {
        return $this->speaker->email;
    }

    public function getCompany()
    {
        return $this->speaker->company ?: null;
    }

    public function getTwitter()
    {
        return $this->speaker->twitter;
    }

    public function getUrl()
    {
        return $this->speaker->url;
    }

    public function getInfo()
    {
        return $this->speaker->info;
    }

    public function getBio()
    {
        return $this->speaker->bio;
    }

    public function getTransportation()
    {
        return $this->speaker->transportation == '1';
    }

    public function getHotel()
    {
        return $this->speaker->hotel;
    }

    public function getAirport()
    {
        return $this->speaker->airport;
    }

    public function getPhoto()
    {
        return $this->speaker->photo_path;
    }

    public function toArrayForApi()
    {
        return [
            'name' => $this->getName(),
            'email' => $this->getEmail(),
            'twitter' => $this->getTwitter(),
            'url' => $this->getUrl(),
            'bio' => $this->getBio(),
        ];
    }
}
