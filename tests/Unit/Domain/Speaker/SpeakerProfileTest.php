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

    /**
     * @test
     */
    public function isAllowedToSeeReturnsFalseIfPropertyIsHidden()
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

    /**
     * @test
     */
    public function isAllowedToSeeReturnsTrueIfPropertyIsNotHidden()
    {
        $property = $this->faker()->word;

        $profile = new SpeakerProfile($this->createUserMock());

        $this->assertTrue($profile->isAllowedToSee($property));
    }

    /**
     * @test
     */
    public function needsProfileReturnsFalseIfUserHasMadeProfile()
    {
        $hasMadeProfile = 1;

        $speaker = $this->createUserMock([
            'has_made_profile' => $hasMadeProfile,
        ]);

        $profile = new SpeakerProfile($speaker);

        $this->assertFalse($profile->needsProfile());
    }

    /**
     * @test
     */
    public function needsProfileReturnsTrueIfUserHasNotMadeProfile()
    {
        $hasMadeProfile = 0;

        $speaker = $this->createUserMock([
            'has_made_profile' => $hasMadeProfile,
        ]);

        $profile = new SpeakerProfile($speaker);

        $this->assertTrue($profile->needsProfile());
    }

    /**
     * @test
     */
    public function getTalksThrowsNotAllowedExceptionIfPropertyIsHidden()
    {
        $hiddenProperties = [
            'talks',
        ];

        $speaker = $this->createUserMock();

        $profile = new SpeakerProfile($speaker, $hiddenProperties);

        $this->expectException(NotAllowedException::class);

        $profile->getTalks();
    }

    /**
     * @test
     */
    public function getTalksReturnsTalksIfPropertyIsNotHidden()
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

    /**
     * @test
     */
    public function getNameThrowsNotAllowedExceptionIfPropertyIsHidden()
    {
        $hiddenProperties = [
            'name',
        ];

        $speaker = $this->createUserMock();

        $profile = new SpeakerProfile($speaker, $hiddenProperties);

        $this->expectException(NotAllowedException::class);

        $profile->getName();
    }

    /**
     * @test
     */
    public function getNameReturnsNameIfPropertyIsNotHidden()
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

    /**
     * @test
     */
    public function getEmailThrowsNotAllowedExceptionIfPropertyIsHidden()
    {
        $hiddenProperties = [
            'email',
        ];

        $speaker = $this->createUserMock();

        $profile = new SpeakerProfile($speaker, $hiddenProperties);

        $this->expectException(NotAllowedException::class);

        $profile->getEmail();
    }

    /**
     * @test
     */
    public function getEmailReturnsEmailIfPropertyIsNotHidden()
    {
        $email = $this->faker()->email;

        $speaker = $this->createUserMock([
            'email' => $email,
        ]);

        $profile = new SpeakerProfile($speaker);

        $this->assertSame($email, $profile->getEmail());
    }

    /**
     * @test
     */
    public function getCompanyThrowsNotAllowedExceptionIfPropertyIsHidden()
    {
        $hiddenProperties = [
            'company',
        ];

        $speaker = $this->createUserMock();

        $profile = new SpeakerProfile($speaker, $hiddenProperties);

        $this->expectException(NotAllowedException::class);

        $profile->getCompany();
    }

    /**
     * @test
     */
    public function getCompanyReturnsCompanyIfPropertyIsNotHidden()
    {
        $company = $this->faker()->company;

        $speaker = $this->createUserMock([
            'company' => $company,
        ]);

        $profile = new SpeakerProfile($speaker);

        $this->assertSame($company, $profile->getCompany());
    }

    /**
     * @test
     */
    public function getTwitterThrowsNotAllowedExceptionIfPropertyIsHidden()
    {
        $hiddenProperties = [
            'twitter',
        ];

        $speaker = $this->createUserMock();

        $profile = new SpeakerProfile($speaker, $hiddenProperties);

        $this->expectException(NotAllowedException::class);

        $profile->getTwitter();
    }

    /**
     * @test
     */
    public function getTwitterReturnsTwitterIfPropertyIsNotHidden()
    {
        $twitter = $this->faker()->userName;

        $speaker = $this->createUserMock([
            'twitter' => $twitter,
        ]);

        $profile = new SpeakerProfile($speaker);

        $this->assertSame($twitter, $profile->getTwitter());
    }

    /**
     * @test
     */
    public function getTwitterUrlThrowsNotAllowedExceptionIfPropertyIsHidden(): void
    {
        $hiddenProperties = [
            'twitter',
        ];

        $speaker = $this->createUserMock();

        $profile = new SpeakerProfile($speaker, $hiddenProperties);

        $this->expectException(NotAllowedException::class);

        $profile->getTwitterUrl();
    }

    /**
     * @test
     * @dataProvider providerEmptyValue
     *
     * @param null|string $value
     */
    public function getTwitterUrlReturnsEmptyStringWhenTwitterPropertyIsNotHiddenButEmpty($value): void
    {
        $speaker = $this->createUserMock([
            'twitter' => $value,
        ]);

        $profile = new SpeakerProfile($speaker);

        $this->assertSame('', $profile->getTwitterUrl());
    }

    public function providerEmptyValue(): array
    {
        $values = [
            'null'         => null,
            'string-empty' => '',
            'string-blank' => '  ',
        ];

        return \array_map(function ($value) {
            return [
                $value,
            ];
        }, $values);
    }

    /**
     * @test
     */
    public function getTwitterUrlReturnsTwitterUrlWhenTwitterPropertyIsNeitherHiddenNorEmpty(): void
    {
        $value = $this->faker()->userName;

        $speaker = $this->createUserMock([
            'twitter' => $value,
        ]);

        $profile = new SpeakerProfile($speaker);

        $expected = 'https://twitter.com/' . $value;

        $this->assertSame($expected, $profile->getTwitterUrl());
    }

    /**
     * @test
     */
    public function getJoindInUsernameThrowsNotAllowedExceptionIfPropertyIsHidden(): void
    {
        $hiddenProperties = [
            'joindin_username',
        ];

        $speaker = $this->createUserMock();

        $profile = new SpeakerProfile($speaker, $hiddenProperties);

        $this->expectException(NotAllowedException::class);

        $profile->getJoindInUsername();
    }

    /**
     * @test
     */
    public function getJoindInUsernameReturnsJoindInUsernameIfPropertyIsNotHidden(): void
    {
        $joindinUsername = $this->faker()->userName;

        $speaker = $this->createUserMock([
            'joindin_username' => $joindinUsername,
        ]);

        $profile = new SpeakerProfile($speaker);

        $this->assertSame($joindinUsername, $profile->getJoindInUsername());
    }

    /**
     * @test
     * @dataProvider providerEmptyValue
     *
     * @param null|string $value
     */
    public function getJoindInUrlThrowsNotAllowedExceptionIfPropertyIsHidden(): void
    {
        $hiddenProperties = [
            'joindin_username',
        ];

        $speaker = $this->createUserMock();

        $profile = new SpeakerProfile($speaker, $hiddenProperties);

        $this->expectException(NotAllowedException::class);

        $profile->getJoindInUrl();
    }

    /**
     * @test
     * @dataProvider providerEmptyValue
     *
     * @param null|string $value
     */
    public function getJoindInUrlReturnsEmptyStringWhenJoindInUsernameIsNotHiddenButEmpty($value): void
    {
        $speaker = $this->createUserMock([
            'joindin_username' => $value,
        ]);

        $profile = new SpeakerProfile($speaker);

        $this->assertSame('', $profile->getJoindInUrl());
    }

    /**
     * @test
     */
    public function getJoindInUrlReturnsJoindInUrlIfPropertyIsNotHidden(): void
    {
        $joindinUsername = $this->faker()->userName;

        $speaker = $this->createUserMock([
            'joindin_username' => $joindinUsername,
        ]);

        $expectedUrl = 'https://joind.in/user/' . $joindinUsername;

        $profile = new SpeakerProfile($speaker);

        $this->assertSame($expectedUrl, $profile->getJoindInUrl());
    }

    /**
     * @test
     */
    public function getUrlThrowsNotAllowedExceptionIfPropertyIsHidden()
    {
        $hiddenProperties = [
            'url',
        ];

        $speaker = $this->createUserMock();

        $profile = new SpeakerProfile($speaker, $hiddenProperties);

        $this->expectException(NotAllowedException::class);

        $profile->getUrl();
    }

    /**
     * @test
     */
    public function getUrlReturnsUrlIfPropertyIsNotHidden()
    {
        $url = $this->faker()->url;

        $speaker = $this->createUserMock([
            'url' => $url,
        ]);

        $profile = new SpeakerProfile($speaker);

        $this->assertSame($url, $profile->getUrl());
    }

    /**
     * @test
     */
    public function getInfoThrowsNotAllowedExceptionIfPropertyIsHidden()
    {
        $hiddenProperties = [
            'info',
        ];

        $speaker = $this->createUserMock();

        $profile = new SpeakerProfile($speaker, $hiddenProperties);

        $this->expectException(NotAllowedException::class);

        $profile->getInfo();
    }

    /**
     * @test
     */
    public function getInfoReturnsInfoIfPropertyIsNotHidden()
    {
        $info = $this->faker()->text;

        $speaker = $this->createUserMock([
            'info' => $info,
        ]);

        $profile = new SpeakerProfile($speaker);

        $this->assertSame($info, $profile->getInfo());
    }

    /**
     * @test
     */
    public function getBioThrowsNotAllowedExceptionIfPropertyIsHidden()
    {
        $hiddenProperties = [
            'bio',
        ];

        $speaker = $this->createUserMock();

        $profile = new SpeakerProfile($speaker, $hiddenProperties);

        $this->expectException(NotAllowedException::class);

        $profile->getBio();
    }

    /**
     * @test
     */
    public function getBioReturnsBioIfPropertyIsNotHidden()
    {
        $bio = $this->faker()->text;

        $speaker = $this->createUserMock([
            'bio' => $bio,
        ]);

        $profile = new SpeakerProfile($speaker);

        $this->assertSame($bio, $profile->getBio());
    }

    /**
     * @test
     */
    public function getTransportationThrowsNotAllowedExceptionIfPropertyIsHidden()
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
     *
     * @test
     */
    public function getTransportationReturnsFalseIfPropertyIsNotHidden($transportation)
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

    /**
     * @test
     */
    public function getTransportationReturnsTrueIfPropertyIsNotHidden()
    {
        $transportation = 1;

        $speaker = $this->createUserMock([
            'transportation' => $transportation,
        ]);

        $profile = new SpeakerProfile($speaker);

        $this->assertTrue($profile->getTransportation());
    }

    /**
     * @test
     */
    public function getHotelThrowsNotAllowedExceptionIfPropertyIsHidden()
    {
        $hiddenProperties = [
            'hotel',
        ];

        $speaker = $this->createUserMock();

        $profile = new SpeakerProfile($speaker, $hiddenProperties);

        $this->expectException(NotAllowedException::class);

        $profile->getHotel();
    }

    /**
     * @test
     */
    public function getHotelReturnsFalseIfPropertyIsNotHidden()
    {
        $hotel = 0;

        $speaker = $this->createUserMock([
            'hotel' => $hotel,
        ]);

        $profile = new SpeakerProfile($speaker);

        $this->assertFalse($profile->getHotel());
    }

    /**
     * @test
     */
    public function getHotelReturnsTrueIfPropertyIsNotHidden()
    {
        $hotel = 1;

        $speaker = $this->createUserMock([
            'hotel' => $hotel,
        ]);

        $profile = new SpeakerProfile($speaker);

        $this->assertTrue($profile->getHotel());
    }

    /**
     * @test
     */
    public function getAirportThrowsNotAllowedExceptionIfPropertyIsHidden()
    {
        $hiddenProperties = [
            'airport',
        ];

        $speaker = $this->createUserMock();

        $profile = new SpeakerProfile($speaker, $hiddenProperties);

        $this->expectException(NotAllowedException::class);

        $profile->getAirport();
    }

    /**
     * @test
     */
    public function getAirportReturnsAirportIfPropertyIsNotHidden()
    {
        $airport = $this->faker()->company;

        $speaker = $this->createUserMock([
            'airport' => $airport,
        ]);

        $profile = new SpeakerProfile($speaker);

        $this->assertSame($airport, $profile->getAirport());
    }

    /**
     * @test
     */
    public function getPhotoThrowsNotAllowedExceptionIfPropertyIsHidden()
    {
        $hiddenProperties = [
            'photo',
        ];

        $speaker = $this->createUserMock();

        $profile = new SpeakerProfile($speaker, $hiddenProperties);

        $this->expectException(NotAllowedException::class);

        $profile->getPhoto();
    }

    /**
     * @test
     */
    public function getPhotoReturnsPhotoIfPropertyIsNotHidden()
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
