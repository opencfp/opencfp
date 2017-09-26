<?php

namespace OpenCFP\Test\Domain\Speaker;

use OpenCFP\Domain\Entity;
use OpenCFP\Domain\Speaker\SpeakerProfile;
use OpenCFP\Test\Util\Faker\GeneratorTrait;

class SpeakerProfileTest extends \PHPUnit\Framework\TestCase
{
    use GeneratorTrait;

    public function testGetNameReturnsFirstAndLastNameCombined()
    {
        $faker = $this->getFaker();

        $firstName = $faker->firstName;
        $lastName = $faker->lastName;

        $speaker = new Entity\User();

        $speaker->set('first_name', $firstName);
        $speaker->set('last_name', $lastName);

        $profile = new SpeakerProfile($speaker);

        $expected = $firstName . ' ' . $lastName;

        $this->assertSame($expected, $profile->getName());
    }

    public function testGetEmailReturnsEmail()
    {
        $email = $this->getFaker()->email;

        $speaker = new Entity\User();

        $speaker->set('email', $email);

        $profile = new SpeakerProfile($speaker);

        $this->assertSame($email, $profile->getEmail());
    }

    public function testGetCompanyReturnsCompany()
    {
        $company = $this->getFaker()->company;

        $speaker = new Entity\User();

        $speaker->set('company', $company);

        $profile = new SpeakerProfile($speaker);

        $this->assertSame($company, $profile->getCompany());
    }

    public function testGetCompanyReturnsNullIfSpeakerHasNoCompany()
    {
        $speaker = new Entity\User();

        $profile = new SpeakerProfile($speaker);

        $this->assertNull($profile->getCompany());
    }

    public function testGetTwitterReturnsTwitter()
    {
        $twitter = $this->getFaker()->userName;

        $speaker = new Entity\User();

        $speaker->set('twitter', $twitter);

        $profile = new SpeakerProfile($speaker);

        $this->assertSame($twitter, $profile->getTwitter());
    }

    public function testGetInfoReturnsInfo()
    {
        $info = $this->getFaker()->text();

        $speaker = new Entity\User();

        $speaker->set('info', $info);

        $profile = new SpeakerProfile($speaker);

        $this->assertSame($info, $profile->getInfo());
    }

    public function testGetBioReturnsBio()
    {
        $bio = $this->getFaker()->text();

        $speaker = new Entity\User();

        $speaker->set('bio', $bio);

        $profile = new SpeakerProfile($speaker);

        $this->assertSame($bio, $profile->getBio());
    }

    public function testGetTransportationReturnsTrueIfValueIsOne()
    {
        $transportation = '1';

        $speaker = new Entity\User();

        $speaker->set('transportation', $transportation);

        $profile = new SpeakerProfile($speaker);

        $this->assertTrue($profile->getTransportation());
    }

    public function testGetTransportationReturnsFalseIfValueIsNotOne()
    {
        $faker = $this->getFaker();

        $transportation = $faker->randomElement([
            0,
            $faker->numberBetween(2),
        ]);

        $speaker = new Entity\User();

        $speaker->set('transportation', $transportation);

        $profile = new SpeakerProfile($speaker);

        $this->assertFalse($profile->getTransportation());
    }

    public function testGetHotelReturnsHotel()
    {
        $hotel = $this->getFaker()->sentence();

        $speaker = new Entity\User();

        $speaker->set('hotel', $hotel);

        $profile = new SpeakerProfile($speaker);

        $this->assertSame($hotel, $profile->getHotel());
    }

    public function testGetAirportReturnsAirport()
    {
        $airport = $this->getFaker()->word;

        $speaker = new Entity\User();

        $speaker->set('airport', $airport);

        $profile = new SpeakerProfile($speaker);

        $this->assertSame($airport, $profile->getAirport());
    }

    public function testGetPhotoReturnsPhotoPath()
    {
        $photoPath = implode('/', $this->getFaker()->words());

        $speaker = new Entity\User();

        $speaker->set('photo_path', $photoPath);

        $profile = new SpeakerProfile($speaker);

        $this->assertSame($photoPath, $profile->getPhoto());
    }

    public function testToArrayForApiReturnsImportantBits()
    {
        $faker = $this->getFaker();

        $firstName = $faker->firstName;
        $lastName = $faker->lastName;
        $email = $faker->email;
        $twitter = $faker->userName;
        $url = $faker->url;
        $bio = $faker->text();

        $speaker = new Entity\User();

        $speaker->set('first_name', $firstName);
        $speaker->set('last_name', $lastName);
        $speaker->set('email', $email);
        $speaker->set('twitter', $twitter);
        $speaker->set('url', $url);
        $speaker->set('bio', $bio);

        $profile = new SpeakerProfile($speaker);

        $expected = [
            'name' => $firstName . ' ' . $lastName,
            'email' => $email,
            'twitter' => $twitter,
            'url' => $url,
            'bio' => $bio,
        ];

        $this->assertSame($expected, $profile->toArrayForApi());
    }
}
