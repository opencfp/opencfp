<?php

require './bootstrap.php';

class SignupFormTest extends PHPUnit_Framework_TestCase
{
    /**
     * Test that form object rejects validation when we don't have
     * enough fields 
     *
     * @test
     */
    public function formRejectsValidationOnMissingFields()
    {
        $data = array(
            'email' => 'test@domain.com',
            'notrequired' => 'test'
        );
        $form = new \OpenCFP\SignupForm($data);
        $response = $form->hasRequiredFields();
        $this->assertFalse($response);
    }

    /**
     * Verify that emails are being validated correctly
     *
     * @test
     * @param string $email
     * @param boolean $expectedResponse
     * @dataProvider emailProvider
     */
    public function emailsAreBeingValidatedCorrectly($email, $expectedResponse)
    {
        $data = array('email' => $email);
        $form = new \OpenCFP\SignupForm($data);
        $this->assertEquals(
            $form->validateEmail(),
            $expectedResponse,
            "Did not validate {$email} as expected"
        );
    }

    /**
     * Data provider for emailsAreBeingValidatedCorrectly
     *
     * @return array
     */
    public function emailProvider()
    {
        return array(
            array('test', false),
            array('test@domain.com', true),
            array('', false),
            array('test@domain', false),
            array('test+tricky@domain.com', true)
        );
    }

    /**
     * Test that passwords are being correctly matched and sanitized
     *
     * @test
     * @param string $passwd
     * @param string $passwd2
     * @param boolean $expectedResponse
     * @dataProvider passwordProvider
     */
    public function passwordsAreBeingCorrectlyMatched($passwd, $passwd2, $expectedResponse)
    {
        $data = array(
            'password' => $passwd,
            'password2' => $passwd2
        );

        $form = new \OpenCFP\SignupForm($data);
        $testResponse = $form->validatePasswords();
        $this->assertEquals(
            $expectedResponse,
            $testResponse,
            "Did not validate passwords as expected"
        );
    }

    /**
     * Data provider for passwordsAreBeingCorrectlyMatched
     *
     * @return array
     */
    public function passwordProvider()
    {
        return array(
            array('foo', 'foo', 'Your password must be at least 5 characters'),
            array('bar', 'foo', "The submitted passwords do not match"),
            array('acceptable', 'acceptable', true),
            array(null, null, "Missing passwords"),
        );
    }

    /**
     * Test that the firstName is being validated correctly
     *
     * @test
     * @param string $firstName
     * @param boolean $expectedResponse
     * @dataProvider firstNameProvider
     */
    public function firstNameIsValidatedCorrectly($firstName, $expectedResponse)
    {
        $data['firstName'] = $firstName;
        $form = new \OpenCFP\SignupForm($data);

        $this->assertEquals(
            $expectedResponse,
            $form->validateFirstName(),
            'Did not validate first name as expected'
        );
    }

    /**
     * Data provider for firstNameIsValidatedCorrectly
     *
     * @return array
     */
    public function firstNameProvider()
    {
        $longName = '';

        for ($x = 1; $x <= 256; $x++) {
            $longName .= 'X';
        }

        return array(
            array('Chris', true),
            array(null, false),
            array('', false),
            array(false, false),
            array($longName, false),
            array("<script>alert('XSS')</script>", false)
        );
    }

    /**
     * Test that the lastName is being validated correctly
     *
     * @test
     * @param string $lastName
     * @param boolean $expectedResponse
     * @dataProvider lastNameProvider
     */
    public function lastNameIsValidatedCorrectly($lastName, $expectedResponse)
    {
        $data['lastName'] = $lastName;
        $form = new \OpenCFP\SignupForm($data);

        $this->assertEquals(
            $expectedResponse,
            $form->validateLastName(),
            'Did not validate first name as expected'
        );
    }

    /**
     * Data provider for lastNameIsValidatedCorrectly
     *
     * @return array
     */
    public function lastNameProvider()
    {
        $longName = '';

        for ($x = 1; $x <= 256; $x++) {
            $longName .= 'X';
        }

        return array(
            array('Chris', true),
            array(null, false),
            array('', false),
            array(false, false),
            array($longName, false),
            array("<script>alert('XSS')</script>", false)
        );
    }

    /**
     * Test that verifies that our wrapper method for validating all
     * fields works correctly
     *
     * @test
     */
    public function validateAllWorksCorrectly()
    {
        $data = array(
            'email' => 'test@domain.com',
            'password' => 'xxxxxx',
            'password2' => 'xxxxxx',
            'firstName' => 'Testy',
            'lastName' => 'McTesterton'
        );
        $form = new \OpenCFP\SignupForm($data);
        $this->assertTrue(
            $form->validateAll(),
            "All form fields did not validate as expected"
        );
    }
}
