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
}
