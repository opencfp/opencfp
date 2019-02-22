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
     * @param string[] $hiddenProperties this is a blacklist, telling the view what fields it isn't allowed to show
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
        return $this->speaker->has_made_profile === 0;
    }

    /**
     * Retrieves all of the speakers talks
     *
     * @throws NotAllowedException
     *
     * @return Talk[]
     */
    public function getTalks(): array
    {
        $this->assertAllowedToSee('talks');

        return $this->speaker->talks->toArray();
    }

    /**
     * @throws NotAllowedException
     *
     * @return string
     */
    public function getName(): string
    {
        $this->assertAllowedToSee('name');

        return $this->speaker->first_name . ' ' . $this->speaker->last_name;
    }

    /**
     * @throws NotAllowedException
     *
     * @return null|string
     */
    public function getEmail()
    {
        $this->assertAllowedToSee('email');

        return $this->speaker->email;
    }

    /**
     * @throws NotAllowedException
     *
     * @return null|string
     */
    public function getCompany()
    {
        $this->assertAllowedToSee('company');

        return $this->speaker->company;
    }

    /**
     * @throws NotAllowedException
     *
     * @return null|string
     */
    public function getTwitter()
    {
        $this->assertAllowedToSee('twitter');

        return $this->speaker->twitter;
    }

    /**
     * @throws NotAllowedException
     *
     * @return string
     */
    public function getTwitterUrl(): string
    {
        $this->assertAllowedToSee('twitter');

        return User::twitterUrl($this->speaker->twitter);
    }

    /**
     * @throws NotAllowedException
     *
     * @return null|string
     */
    public function getJoindInUsername()
    {
        $this->assertAllowedToSee('joindin_username');

        return $this->speaker->joindin_username;
    }

    /**
     * @throws NotAllowedException
     *
     * @return null|string
     */
    public function getJoindInUrl()
    {
        $this->assertAllowedToSee('joindin_username');

        $username = $this->speaker->joindin_username;

        if ($username === null || \trim($username) === '') {
            return '';
        }

        return 'https://joind.in/user/' . $username;
    }

    /**
     * @throws NotAllowedException
     *
     * @return null|string
     */
    public function getUrl()
    {
        $this->assertAllowedToSee('url');

        return $this->speaker->url;
    }

    /**
     * @throws NotAllowedException
     *
     * @return null|string
     */
    public function getInfo()
    {
        $this->assertAllowedToSee('info');

        return $this->speaker->info;
    }

    /**
     * @throws NotAllowedException
     *
     * @return null|string
     */
    public function getBio()
    {
        $this->assertAllowedToSee('bio');

        return $this->speaker->bio;
    }

    /**
     * @throws NotAllowedException
     *
     * @return bool
     */
    public function getTransportation(): bool
    {
        $this->assertAllowedToSee('transportation');

        return $this->speaker->transportation === 1;
    }

    /**
     * @throws NotAllowedException
     *
     * @return bool
     */
    public function getHotel(): bool
    {
        $this->assertAllowedToSee('hotel');

        return $this->speaker->hotel === 1;
    }

    /**
     * @throws NotAllowedException
     *
     * @return null|string
     */
    public function getAirport()
    {
        $this->assertAllowedToSee('airport');

        return $this->speaker->airport;
    }

    /**
     * @throws NotAllowedException
     *
     * @return null|string
     */
    public function getPhoto()
    {
        $this->assertAllowedToSee('photo');

        return $this->speaker->photo_path;
    }
}
