<?php

namespace OpenCFP\Test\Integration\Domain\Speaker;

use OpenCFP\Domain\Model\User;
use OpenCFP\Domain\Speaker\NotAllowedException;
use OpenCFP\Domain\Speaker\SpeakerProfile;
use OpenCFP\Test\BaseTestCase;
use OpenCFP\Test\Helper\RefreshDatabase;

/**
 * @coversNothing
 */
class SpeakerProfileTest extends BaseTestCase
{
    use RefreshDatabase;

    private static $user;

    /**
     * @var SpeakerProfile
     */
    private static $profile;

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();

        self::$user    = factory(User::class, 1)->create(['has_made_profile' => 1])->first();
        self::$profile = new SpeakerProfile(self::$user);
    }

    public function testNeedsProfileReturnsCorrectly()
    {
        //if the user needs a profile they haven't made one, hence the !
        $this->assertSame(!self::$user->has_made_profile, self::$profile->needsProfile());
    }

    public function testGetNameReturnsFirstAndLastNameCombined()
    {
        $firstName = self::$user->first_name;
        $lastName  = self::$user->last_name;
        $expected  = $firstName . ' ' . $lastName;

        $this->assertSame($expected, self::$profile->getName());
    }

    public function testGetEmailReturnsEmail()
    {
        $email = self::$user->email;

        $this->assertSame($email, self::$profile->getEmail());
    }

    public function testGetCompanyReturnsCompany()
    {
        $company = self::$user->company;

        $this->assertSame($company, self::$profile->getCompany());
    }

    public function testGetTwitterReturnsTwitter()
    {
        $twitter = self::$user->twitter;

        $this->assertSame($twitter, self::$profile->getTwitter());
    }

    public function testGetInfoReturnsInfo()
    {
        $info = self::$user->info;

        $this->assertSame($info, self::$profile->getInfo());
    }

    public function testGetBioReturnsBio()
    {
        $bio = self::$user->bio;

        $this->assertSame($bio, self::$profile->getBio());
    }

    public function testGetTransportationReturnsTransportation()
    {
        $transportation = self::$user->transportation ? true : false;

        $this->assertSame($transportation, self::$profile->getTransportation());
    }

    public function testGetHotelReturnsHotel()
    {
        $hotel = self::$user->hotel;

        $this->assertSame($hotel, self::$profile->getHotel());
    }

    public function testGetAirportReturnsAirport()
    {
        $airport = self::$user->airport;

        $this->assertSame($airport, self::$profile->getAirport());
    }

    public function testGetPhotoReturnsPhotoPath()
    {
        $photoPath = self::$user->photo_path;

        $this->assertSame($photoPath, self::$profile->getPhoto());
    }

    public function testIsAllowedtoSeeReturnsFalseIfEntryIsAHiddenProperty()
    {
        $profile = new SpeakerProfile(self::$user, ['email', 'twitter']);

        $this->assertFalse($profile->isAllowedToSee('email'));
        $this->assertFalse($profile->isAllowedToSee('twitter'));
        $this->assertTrue($profile->isAllowedToSee('hotel'));
    }

    public function testErrorGetsThrownWhenGettingAFieldThatIsNotAllowed()
    {
        $profile = new SpeakerProfile(self::$user, ['email', 'twitter']);
        //Check we are still allowed to see other items
        $photoPath = self::$user->photo_path;

        $this->assertSame($photoPath, self::$profile->getPhoto());

        //Check we get an exception when viewing something not allowed.
        $this->expectException(NotAllowedException::class);
        $profile->getEmail();
    }

    public function testToArrayForApiReturnsImportantBits()
    {
        $firstName = self::$user->first_name;
        $lastName  = self::$user->last_name;
        $email     = self::$user->email;
        $twitter   = self::$user->twitter;
        $url       = self::$user->url;
        $bio       = self::$user->bio;

        $expected = [
            'name'    => $firstName . ' ' . $lastName,
            'email'   => $email,
            'twitter' => $twitter,
            'url'     => $url,
            'bio'     => $bio,
        ];

        $this->assertSame($expected, self::$profile->toArrayForApi());
    }
}
