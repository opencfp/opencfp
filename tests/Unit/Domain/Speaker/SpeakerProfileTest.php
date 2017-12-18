<?php

declare(strict_types=1);

/**
 * Copyright (c) 2013-2017 OpenCFP
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @see https://github.com/opencfp/opencfp
 */

namespace OpenCFP\Test\Unit\Domain\Speaker;

use Illuminate\Support\Collection;
use Localheinz\Test\Util\Helper;
use OpenCFP\Domain\Model;
use OpenCFP\Domain\Speaker\NotAllowedException;
use OpenCFP\Domain\Speaker\SpeakerProfile;
use PHPUnit\Framework;

final class SpeakerProfileTest extends Framework\TestCase
{
    use Helper;

    public function testIsAllowedToSeeReturnsFalseIfPropertyIsHidden()
    {
        $property = $this->faker()->word;

        $hiddenProperties = [
            $property,
        ];

        $profile = new SpeakerProfile(
            $this->createUserMock(),
            $hiddenProperties
        );

        $this->assertFalse($profile->isAllowedToSee($property));
    }

    public function testIsAllowedToSeeReturnsTrueIfPropertyIsNotHidden()
    {
        $property = $this->faker()->word;

        $profile = new SpeakerProfile($this->createUserMock());

        $this->assertTrue($profile->isAllowedToSee($property));
    }

    public function testNeedsProfileReturnsFalseIfUserHasMadeProfile()
    {
        $hasMadeProfile = 1;

        $speaker = $this->createUserMock([
            'has_made_profile' => $hasMadeProfile,
        ]);

        $profile = new SpeakerProfile($speaker);

        $this->assertFalse($profile->needsProfile());
    }

    public function testNeedsProfileReturnsTrueIfUserHasNotMadeProfile()
    {
        $hasMadeProfile = 0;

        $speaker = $this->createUserMock([
            'has_made_profile' => $hasMadeProfile,
        ]);

        $profile = new SpeakerProfile($speaker);

        $this->assertTrue($profile->needsProfile());
    }

    public function testGetTalksThrowsNotAllowedExceptionIfPropertyIsHidden()
    {
        $hiddenProperties = [
            'talks',
        ];

        $speaker = $this->createUserMock();

        $profile = new SpeakerProfile($speaker, $hiddenProperties);

        $this->expectException(NotAllowedException::class);

        $profile->getTalks();
    }

    public function testGetTalksReturnsTalksIfPropertyIsNotHidden()
    {
        $talks = [
            $this->createTalkMock(),
            $this->createTalkMock(),
            $this->createTalkMock(),
        ];
        $collection = $this->createMock(Collection::class);
        $collection->method('toArray')->willReturn($talks);

        $speaker = $this->createUserMock([
            'talks' => $collection,
        ]);

        $profile = new SpeakerProfile($speaker);

        $this->assertSame($talks, $profile->getTalks());
    }

    public function testGetNameThrowsNotAllowedExceptionIfPropertyIsHidden()
    {
        $hiddenProperties = [
            'name',
        ];

        $speaker = $this->createUserMock();

        $profile = new SpeakerProfile($speaker, $hiddenProperties);

        $this->expectException(NotAllowedException::class);

        $profile->getName();
    }

    public function testGetNameReturnsNameIfPropertyIsNotHidden()
    {
        $faker = $this->faker();

        $firstName = $faker->firstName;
        $lastName  = $faker->lastName;

        $speaker = $this->createUserMock([
            'first_name' => $firstName,
            'last_name'  => $lastName,
        ]);

        $profile = new SpeakerProfile($speaker);

        $name = \sprintf(
            '%s %s',
            $firstName,
            $lastName
        );

        $this->assertSame($name, $profile->getName());
    }

    public function testGetEmailThrowsNotAllowedExceptionIfPropertyIsHidden()
    {
        $hiddenProperties = [
            'email',
        ];

        $speaker = $this->createUserMock();

        $profile = new SpeakerProfile($speaker, $hiddenProperties);

        $this->expectException(NotAllowedException::class);

        $profile->getEmail();
    }

    public function testGetEmailReturnsEmailIfPropertyIsNotHidden()
    {
        $email = $this->faker()->email;

        $speaker = $this->createUserMock([
            'email' => $email,
        ]);

        $profile = new SpeakerProfile($speaker);

        $this->assertSame($email, $profile->getEmail());
    }

    public function testGetCompanyThrowsNotAllowedExceptionIfPropertyIsHidden()
    {
        $hiddenProperties = [
            'company',
        ];

        $speaker = $this->createUserMock();

        $profile = new SpeakerProfile($speaker, $hiddenProperties);

        $this->expectException(NotAllowedException::class);

        $profile->getCompany();
    }

    public function testGetCompanyReturnsCompanyIfPropertyIsNotHidden()
    {
        $company = $this->faker()->company;

        $speaker = $this->createUserMock([
            'company' => $company,
        ]);

        $profile = new SpeakerProfile($speaker);

        $this->assertSame($company, $profile->getCompany());
    }

    public function testGetTwitterThrowsNotAllowedExceptionIfPropertyIsHidden()
    {
        $hiddenProperties = [
            'twitter',
        ];

        $speaker = $this->createUserMock();

        $profile = new SpeakerProfile($speaker, $hiddenProperties);

        $this->expectException(NotAllowedException::class);

        $profile->getTwitter();
    }

    public function testGetTwitterReturnsTwitterIfPropertyIsNotHidden()
    {
        $twitter = $this->faker()->userName;

        $speaker = $this->createUserMock([
            'twitter' => $twitter,
        ]);

        $profile = new SpeakerProfile($speaker);

        $this->assertSame($twitter, $profile->getTwitter());
    }

    public function testGetUrlThrowsNotAllowedExceptionIfPropertyIsHidden()
    {
        $hiddenProperties = [
            'url',
        ];

        $speaker = $this->createUserMock();

        $profile = new SpeakerProfile($speaker, $hiddenProperties);

        $this->expectException(NotAllowedException::class);

        $profile->getUrl();
    }

    public function testGetUrlReturnsUrlIfPropertyIsNotHidden()
    {
        $url = $this->faker()->url;

        $speaker = $this->createUserMock([
            'url' => $url,
        ]);

        $profile = new SpeakerProfile($speaker);

        $this->assertSame($url, $profile->getUrl());
    }

    public function testGetInfoThrowsNotAllowedExceptionIfPropertyIsHidden()
    {
        $hiddenProperties = [
            'info',
        ];

        $speaker = $this->createUserMock();

        $profile = new SpeakerProfile($speaker, $hiddenProperties);

        $this->expectException(NotAllowedException::class);

        $profile->getInfo();
    }

    public function testGetInfoReturnsInfoIfPropertyIsNotHidden()
    {
        $info = $this->faker()->text;

        $speaker = $this->createUserMock([
            'info' => $info,
        ]);

        $profile = new SpeakerProfile($speaker);

        $this->assertSame($info, $profile->getInfo());
    }

    public function testGetBioThrowsNotAllowedExceptionIfPropertyIsHidden()
    {
        $hiddenProperties = [
            'bio',
        ];

        $speaker = $this->createUserMock();

        $profile = new SpeakerProfile($speaker, $hiddenProperties);

        $this->expectException(NotAllowedException::class);

        $profile->getBio();
    }

    public function testGetBioReturnsBioIfPropertyIsNotHidden()
    {
        $bio = $this->faker()->text;

        $speaker = $this->createUserMock([
            'bio' => $bio,
        ]);

        $profile = new SpeakerProfile($speaker);

        $this->assertSame($bio, $profile->getBio());
    }

    public function testGetTransportationThrowsNotAllowedExceptionIfPropertyIsHidden()
    {
        $hiddenProperties = [
            'transportation',
        ];

        $speaker = $this->createUserMock();

        $profile = new SpeakerProfile($speaker, $hiddenProperties);

        $this->expectException(NotAllowedException::class);

        $profile->getTransportation();
    }

    /**
     * @dataProvider providerDoesNotNeedTransportation
     *
     * @param mixed $transportation
     */
    public function testGetTransportationReturnsFalseIfPropertyIsNotHidden($transportation)
    {
        $speaker = $this->createUserMock([
            'transportation' => $transportation,
        ]);

        $profile = new SpeakerProfile($speaker);

        $this->assertFalse($profile->getTransportation());
    }

    public function providerDoesNotNeedTransportation(): \Generator
    {
        $values = [
            'int-zero' => 0,
            'null'     => null,
        ];

        foreach ($values as $key => $value) {
            yield $key => [
                $value,
            ];
        }
    }

    public function testGetTransportationReturnsTrueIfPropertyIsNotHidden()
    {
        $transportation = 1;

        $speaker = $this->createUserMock([
            'transportation' => $transportation,
        ]);

        $profile = new SpeakerProfile($speaker);

        $this->assertTrue($profile->getTransportation());
    }

    public function testGetHotelThrowsNotAllowedExceptionIfPropertyIsHidden()
    {
        $hiddenProperties = [
            'hotel',
        ];

        $speaker = $this->createUserMock();

        $profile = new SpeakerProfile($speaker, $hiddenProperties);

        $this->expectException(NotAllowedException::class);

        $profile->getHotel();
    }

    public function testGetHotelReturnsFalseIfPropertyIsNotHidden()
    {
        $hotel = 0;

        $speaker = $this->createUserMock([
            'hotel' => $hotel,
        ]);

        $profile = new SpeakerProfile($speaker);

        $this->assertFalse($profile->getHotel());
    }

    public function testGetHotelReturnsTrueIfPropertyIsNotHidden()
    {
        $hotel = 1;

        $speaker = $this->createUserMock([
            'hotel' => $hotel,
        ]);

        $profile = new SpeakerProfile($speaker);

        $this->assertTrue($profile->getHotel());
    }

    public function testGetAirportThrowsNotAllowedExceptionIfPropertyIsHidden()
    {
        $hiddenProperties = [
            'airport',
        ];

        $speaker = $this->createUserMock();

        $profile = new SpeakerProfile($speaker, $hiddenProperties);

        $this->expectException(NotAllowedException::class);

        $profile->getAirport();
    }

    public function testGetAirportReturnsAirportIfPropertyIsNotHidden()
    {
        $airport = $this->faker()->company;

        $speaker = $this->createUserMock([
            'airport' => $airport,
        ]);

        $profile = new SpeakerProfile($speaker);

        $this->assertSame($airport, $profile->getAirport());
    }

    public function testGetPhotoThrowsNotAllowedExceptionIfPropertyIsHidden()
    {
        $hiddenProperties = [
            'photo',
        ];

        $speaker = $this->createUserMock();

        $profile = new SpeakerProfile($speaker, $hiddenProperties);

        $this->expectException(NotAllowedException::class);

        $profile->getPhoto();
    }

    public function testGetPhotoReturnsPhotoIfPropertyIsNotHidden()
    {
        $photo = $this->faker()->url;

        $speaker = $this->createUserMock([
            'photo_path' => $photo,
        ]);

        $profile = new SpeakerProfile($speaker);

        $this->assertSame($photo, $profile->getPhoto());
    }

    /**
     * @deprecated
     *
     * @param array $properties
     *
     * @return Model\User|\PHPUnit_Framework_MockObject_MockObject
     */
    private function createUserMock(array $properties = []): Model\User
    {
        $user = $this->createMock(Model\User::class);

        $user
            ->expects($this->any())
            ->method('__get')
            ->willReturnCallback(function (string $property) use ($properties) {
                if (\array_key_exists($property, $properties)) {
                    return $properties[$property];
                }
            });

        return $user;
    }

    /**
     * @deprecated
     *
     * @return Model\Talk|\PHPUnit_Framework_MockObject_MockObject
     */
    private function createTalkMock(): Model\Talk
    {
        return $this->createMock(Model\Talk::class);
    }
}
