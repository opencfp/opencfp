<?php

/**
 * Copyright (c) 2013-2017 OpenCFP
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @see https://github.com/opencfp/opencfp
 */

namespace OpenCFP\Test\Unit\Domain\Speaker;

use OpenCFP\Domain\Model;
use OpenCFP\Domain\Speaker\NotAllowedException;
use OpenCFP\Domain\Speaker\SpeakerProfile;
use OpenCFP\Test\Helper\Faker\GeneratorTrait;
use PHPUnit\Framework;

/**
 * @covers \OpenCFP\Domain\Speaker\SpeakerProfile
 */
final class SpeakerProfileTest extends Framework\TestCase
{
    use GeneratorTrait;

    public function testIsAllowedToSeeReturnsFalseIfPropertyIsHidden()
    {
        $property = $this->getFaker()->word;

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
        $property = $this->getFaker()->word;

        $profile = new SpeakerProfile($this->createUserMock());

        $this->assertTrue($profile->isAllowedToSee($property));
    }

    /**
     * @dataProvider providerHasMadeProfile
     *
     * @param mixed $hasMadeProfile
     */
    public function testNeedsProfileReturnsFalseIfUserHasMadeProfile($hasMadeProfile)
    {
        $speaker = $this->createUserMock([
            'has_made_profile' => $hasMadeProfile,
        ]);

        $profile = new SpeakerProfile($speaker);

        $this->assertFalse($profile->needsProfile());
    }

    public function providerHasMadeProfile(): \Generator
    {
        $faker = $this->getFaker();

        $values = [
            'boolean-true'  => true,
            'float'         => $faker->randomFloat(3, 0.1),
            'int'           => $faker->numberBetween(1),
            'string-number' => '1',
        ];

        foreach ($values as $key => $value) {
            yield $key => [
                $value,
            ];
        }
    }

    /**
     * @dataProvider providerHasNotMadeProfile
     *
     * @param mixed $hasMadeProfile
     */
    public function testNeedsProfileReturnsTrueIfUserHasNotMadeProfile($hasMadeProfile)
    {
        $speaker = $this->createUserMock([
            'has_made_profile' => $hasMadeProfile,
        ]);

        $profile = new SpeakerProfile($speaker);

        $this->assertTrue($profile->needsProfile());
    }

    public function providerHasNotMadeProfile(): \Generator
    {
        $values = [
            'boolean-false' => false,
            'float-zero'    => 0.0,
            'int-zero'      => 0,
            'null'          => null,
            'string-zero'   => '0',
        ];

        foreach ($values as $key => $value) {
            yield $key => [
                $value,
            ];
        }
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

        $speaker = $this->createUserMock([
            'talks' => $talks,
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
        $faker = $this->getFaker();

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
        $email = $this->getFaker()->email;

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
        $company = $this->getFaker()->company;

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
        $twitter = $this->getFaker()->userName;

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
        $url = $this->getFaker()->url;

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
        $info = $this->getFaker()->text;

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
        $bio = $this->getFaker()->text;

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
        $faker = $this->getFaker();

        $values = [
            'boolean-false' => false,
            'float-zero'    => 0.0,
            'int-zero'      => 0,
            'null'          => null,
            'string'        => $faker->word,
            'string-zero'   => '0',
        ];

        foreach ($values as $key => $value) {
            yield $key => [
                $value,
            ];
        }
    }

    /**
     * @dataProvider providerNeedsTransportation
     *
     * @param mixed $transportation
     */
    public function testGetTransportationReturnsTrueIfPropertyIsNotHidden($transportation)
    {
        $speaker = $this->createUserMock([
            'transportation' => $transportation,
        ]);

        $profile = new SpeakerProfile($speaker);

        $this->assertTrue($profile->getTransportation());
    }

    public function providerNeedsTransportation(): \Generator
    {
        $values = [
            'boolean-true'  => true,
            'string-number' => '1',
        ];

        foreach ($values as $key => $value) {
            yield $key => [
                $value,
            ];
        }
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

    public function testGetHotelReturnsHotelIfPropertyIsNotHidden()
    {
        $hotel = $this->getFaker()->company;

        $speaker = $this->createUserMock([
            'hotel' => $hotel,
        ]);

        $profile = new SpeakerProfile($speaker);

        $this->assertSame($hotel, $profile->getHotel());
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
        $airport = $this->getFaker()->company;

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
        $photo = $this->getFaker()->url;

        $speaker = $this->createUserMock([
            'photo_path' => $photo,
        ]);

        $profile = new SpeakerProfile($speaker);

        $this->assertSame($photo, $profile->getPhoto());
    }

    /**
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
     * @return Model\Talk|\PHPUnit_Framework_MockObject_MockObject
     */
    private function createTalkMock(): Model\Talk
    {
        return $this->createMock(Model\Talk::class);
    }
}
