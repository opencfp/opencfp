<?php

namespace OpenCFP\Test\Http\Form\Entity;

use OpenCFP\Http\Form\Entity\User;

class UserTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var User
     */
    protected $userEntity;

    protected function setUp()
    {
        parent::setUp();

        $this->userEntity = new User();
    }

    public function tearDown()
    {
        parent::tearDown();
    }

    /**
     * Test that the entity can return an array of its stored values.
     *
     * @test
     */
    public function canReturnAnArrayOfStoredValues()
    {
        $expectedValuesArray = [
            'email' => 'you@domain.org',
            'password' => 'test',
            'permissions' => 'guest',
            'last_login' => '03-20-2017',
            'first_name' => 'John',
            'last_name' => 'Doe',
            'created_at' => '03-20-2017',
            'updated_at' => '03-20-2017',
            'company' => 'ACME',
            'twitter' => '@twitter',
            'airport' => 'AAA',
            'url' => 'http://www.example.com',
            'transportation' => true,
            'hotel' => true,
            'bio' => 'Who is this guy?',
            'info' => 'Some info.',
            'photo_path' => '/tmp/somefolder/image.png',
            'agree_coc' => true,
            'id' => 1,
        ];

        $this->userEntity->setEmail($expectedValuesArray['email']);
        $this->userEntity->setPassword($expectedValuesArray['password']);
        $this->userEntity->setPermissions($expectedValuesArray['permissions']);
        $this->userEntity->setLastLogin($expectedValuesArray['last_login']);
        $this->userEntity->setFirstName($expectedValuesArray['first_name']);
        $this->userEntity->setLastName($expectedValuesArray['last_name']);
        $this->userEntity->setCreatedAt($expectedValuesArray['created_at']);
        $this->userEntity->setUpdatedAt($expectedValuesArray['updated_at']);
        $this->userEntity->setCompany($expectedValuesArray['company']);
        $this->userEntity->setTwitter($expectedValuesArray['twitter']);
        $this->userEntity->setAirport($expectedValuesArray['airport']);
        $this->userEntity->setUrl($expectedValuesArray['url']);
        $this->userEntity->setTransportation($expectedValuesArray['transportation']);
        $this->userEntity->setHotel($expectedValuesArray['hotel']);
        $this->userEntity->setBio($expectedValuesArray['bio']);
        $this->userEntity->setInfo($expectedValuesArray['info']);
        $this->userEntity->setPhotoPath($expectedValuesArray['photo_path']);
        $this->userEntity->setAgreeCoc($expectedValuesArray['agree_coc']);
        $this->userEntity->setId($expectedValuesArray['id']);

        $this->assertSame(
            $expectedValuesArray['email'],
            $this->userEntity->getEmail()
        );
        $this->assertSame(
            $expectedValuesArray['password'],
            $this->userEntity->getPassword()
        );
        $this->assertSame(
            $expectedValuesArray['permissions'],
            $this->userEntity->getPermissions()
        );
        $this->assertSame(
            $expectedValuesArray['last_login'],
            $this->userEntity->getLastLogin()
        );
        $this->assertSame(
            $expectedValuesArray['first_name'],
            $this->userEntity->getFirstName()
        );
        $this->assertSame(
            $expectedValuesArray['last_name'],
            $this->userEntity->getLastName()
        );
        $this->assertSame(
            $expectedValuesArray['created_at'],
            $this->userEntity->getCreatedAt()
        );
        $this->assertSame(
            $expectedValuesArray['updated_at'],
            $this->userEntity->getUpdatedAt()
        );
        $this->assertSame(
            $expectedValuesArray['company'],
            $this->userEntity->getCompany()
        );
        $this->assertSame(
            $expectedValuesArray['twitter'],
            $this->userEntity->getTwitter()
        );
        $this->assertSame(
            $expectedValuesArray['airport'],
            $this->userEntity->getAirport()
        );
        $this->assertSame(
            $expectedValuesArray['url'],
            $this->userEntity->getUrl()
        );
        $this->assertSame(
            $expectedValuesArray['transportation'],
            $this->userEntity->getTransportation()
        );
        $this->assertSame(
            $expectedValuesArray['hotel'],
            $this->userEntity->getHotel()
        );
        $this->assertSame(
            $expectedValuesArray['bio'],
            $this->userEntity->getBio()
        );
        $this->assertSame(
            $expectedValuesArray['info'],
            $this->userEntity->getInfo()
        );
        $this->assertSame(
            $expectedValuesArray['photo_path'],
            $this->userEntity->getPhotoPath()
        );
        $this->assertSame(
            $expectedValuesArray['agree_coc'],
            $this->userEntity->getAgreeCoc()
        );
        $this->assertSame(
            $expectedValuesArray['id'],
            $this->userEntity->getId()
        );

        $this->assertEquals($expectedValuesArray, $this->userEntity->createArray());
    }
}
