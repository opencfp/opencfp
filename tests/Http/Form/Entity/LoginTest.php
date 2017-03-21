<?php

namespace OpenCFP\Test\Http\Form\Entity;

use OpenCFP\Http\Form\Entity\Login as LoginEntity;

class LoginTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var LoginEntity
     */
    protected $loginEntity;

    protected function setUp()
    {
        $this->loginEntity = new LoginEntity();
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
        ];

        $this->loginEntity->setEmail($expectedValuesArray['email']);
        $this->loginEntity->setPassword($expectedValuesArray['password']);

        $this->assertSame(
            $expectedValuesArray['email'],
            $this->loginEntity->getEmail()
        );
        $this->assertSame(
            $expectedValuesArray['password'],
            $this->loginEntity->getPassword()
        );

        $this->assertEquals($expectedValuesArray, $this->loginEntity->createArray());
    }
}
