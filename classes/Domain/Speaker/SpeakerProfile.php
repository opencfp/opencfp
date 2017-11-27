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
    private $speaker;

    /**
     * @var array
     */
    private $hiddenProperties;

    /**
     * @param User     $speaker
     * @param string[] $hiddenProperties This is a blacklist, telling the view what fields it isn't allowed to show.
     */
    public function __construct(User $speaker, array $hiddenProperties = [])
    {
        $this->speaker          = $speaker;
        $this->hiddenProperties = $hiddenProperties;
    }

    public function isAllowedToSee(string $property): bool
    {
        return !\in_array($property, $this->hiddenProperties);
    }

    private function assertAllowedToSee(string $property)
    {
        if (!$this->isAllowedToSee($property)) {
            throw NotAllowedException::notAllowedToView($property);
        }
    }

    public function needsProfile(): bool
    {
        return $this->speaker->has_made_profile == 0;
    }

    /**
     * Retrieves all of the speakers talks
     *
     * @return Talk[]
     */
    public function getTalks()
    {
        $this->assertAllowedToSee('talks');

        return $this->speaker->talks;
    }

    public function getName(): string
    {
        $this->assertAllowedToSee('name');

        return $this->speaker->first_name . ' ' . $this->speaker->last_name;
    }

    public function getEmail()
    {
        $this->assertAllowedToSee('email');

        return $this->speaker->email;
    }

    public function getCompany()
    {
        $this->assertAllowedToSee('company');

        return $this->speaker->company ?: null;
    }

    public function getTwitter()
    {
        $this->assertAllowedToSee('twitter');

        return $this->speaker->twitter;
    }

    public function getUrl()
    {
        $this->assertAllowedToSee('url');

        return $this->speaker->url;
    }

    public function getInfo()
    {
        $this->assertAllowedToSee('info');

        return $this->speaker->info;
    }

    public function getBio()
    {
        $this->assertAllowedToSee('bio');

        return $this->speaker->bio;
    }

    public function getTransportation()
    {
        $this->assertAllowedToSee('transportation');

        return $this->speaker->transportation == '1';
    }

    public function getHotel()
    {
        $this->assertAllowedToSee('hotel');

        return $this->speaker->hotel;
    }

    public function getAirport()
    {
        $this->assertAllowedToSee('airport');

        return $this->speaker->airport;
    }

    public function getPhoto()
    {
        $this->assertAllowedToSee('photo');

        return $this->speaker->photo_path;
    }

    public function toArrayForApi()
    {
        return [
            'name'    => $this->getName(),
            'email'   => $this->getEmail(),
            'twitter' => $this->getTwitter(),
            'url'     => $this->getUrl(),
            'bio'     => $this->getBio(),
        ];
    }
}
