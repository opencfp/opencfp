<?php

namespace OpenCFP\Test\Http\Form\Entity;

use OpenCFP\Http\Form\Entity\Profile;

class ProfileTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Profile
     */
    protected $profileEntity;

    protected function setUp()
    {
        parent::setUp();

        $this->profileEntity = new Profile();
    }

    public function tearDown()
    {
        parent::tearDown();
    }

    /**
     * Test that the entity can store and return all of its values.
     *
     * @test
     */
    public function canTransformEloquentUser()
    {
        $fakeEloquentUser = new \stdClass();
        $fakeEloquentUser->id = 1;
        $fakeEloquentUser->email = 'you@domain.org';
        $fakeEloquentUser->first_name = 'John';
        $fakeEloquentUser->last_name = 'Doe';
        $fakeEloquentUser->company = 'ACME';
        $fakeEloquentUser->twitter = '@twitter';
        $fakeEloquentUser->airport = 'AAA';
        $fakeEloquentUser->transportation = true;
        $fakeEloquentUser->hotel = true;
        $fakeEloquentUser->bio = 'Who is this guy?';
        $fakeEloquentUser->info = 'Some info.';
        $fakeEloquentUser->photo_path = '/tmp/somefolder/image.png';

        $this->profileEntity->transformEloquentUser($fakeEloquentUser);

        $this->assertSame($fakeEloquentUser->id, $this->profileEntity->getId());
        $this->assertSame($fakeEloquentUser->email, $this->profileEntity->getEmail());
        $this->assertSame($fakeEloquentUser->first_name, $this->profileEntity->getFirstName());
        $this->assertSame($fakeEloquentUser->last_name, $this->profileEntity->getLastName());
        $this->assertSame($fakeEloquentUser->company, $this->profileEntity->getCompany());
        $this->assertSame($fakeEloquentUser->twitter, $this->profileEntity->getTwitter());
        $this->assertSame($fakeEloquentUser->airport, $this->profileEntity->getAirport());
        $this->assertSame($fakeEloquentUser->transportation, $this->profileEntity->getTransportation());
        $this->assertSame($fakeEloquentUser->hotel, $this->profileEntity->getHotel());
        $this->assertSame($fakeEloquentUser->bio, $this->profileEntity->getBio());
        $this->assertSame($fakeEloquentUser->info, $this->profileEntity->getInfo());
        $this->assertSame($fakeEloquentUser->photo_path, $this->profileEntity->getPhotoPath());
    }
}
