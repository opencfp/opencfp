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
}
