<?php

class SignupFormTest extends \PHPUnit_Framework_TestCase
{
    private $purifier;

    protected function setUp()
    {
        $this->purifier = new \HTMLPurifier();
    }

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
        $form = new \OpenCFP\Form\SignupForm($data, $this->purifier);
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
        $form = new \OpenCFP\Form\SignupForm($data, $this->purifier);
        $this->assertEquals(
            $form->validateEmail(),
            $expectedResponse,
            "Did not validate {$email} as expected"
        );
    }

    /**
     * Validation should fail if we don't have an email address in the data
     * and try to validate it
     *
     * @test
     */
    public function emailValidationShouldFailWithoutEmail()
    {
        $data = array();
        $form = new \OpenCFP\Form\SignupForm($data, $this->purifier);
        $this->assertFalse(
            $form->validateEmail(),
            "Validating empty email did not fail"
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
     * Data provider for properPasswordsPassValidationAndSanitization
     *
     * @return array
     */
    public function properPasswordValidator()
    {
        return array(
            array('acceptable'),
            array('testing123'),
            array('{^secur3'),
            array('invalidChars&*$')
        );
    }

    /**
     * Test that password that match and are of the proper length pass validation
     * and sanitization
     *
     * @test
     * @param string $passwd
     * @dataProvider properPasswordValidator
     */
    public function properPasswordsPassValidationAndSanitization($passwd)
    {
        $data = array(
            'password' => $passwd,
            'password2' => $passwd 
        );
        $form = new \OpenCFP\Form\SignupForm($data, $this->purifier);
        $form->sanitize();

        $this->assertTrue(
            $form->validatePasswords(),
            "Valid passwords did not survive validation and sanitization"
        );
    }

    /**
     * Test that bad passwords are being correctly matched and sanitized
     *
     * @test
     * @param string $passwd
     * @param string $passwd2
     * @param string $expectedMessage
     * @param boolean $expectedResponse
     * @dataProvider badPasswordProvider
     */
    public function badPasswordsAreBeingCorrectlyDetected($passwd, $passwd2, $expectedMessage, $expectedResponse)
    {
        $data = array(
            'password' => $passwd,
            'password2' => $passwd2
        );

        $form = new \OpenCFP\Form\SignupForm($data, $this->purifier);
        $form->sanitize();
        $testResponse = $form->validatePasswords();

        $this->assertEquals($expectedResponse, $testResponse);
        $this->assertContains(
            $expectedMessage,
            $form->error_messages,
            "Did not get expected error message"
        );
    }

    /**
     * Data provider for passwordsAreBeingCorrectlyMatched
     *
     * @return array
     */
    public function badPasswordProvider()
    {
        return array(
            array('foo', 'foo', "The submitted password must be at least 5 characters long", false),
            array('bar', 'foo', "The submitted passwords do not match", false),
            array(null, null, "Missing passwords", false),
            array('password with spaces', 'password with spaces', "The submitted password contains invalid characters", false),
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
        $data['first_name'] = $firstName;
        $form = new \OpenCFP\Form\SignupForm($data, $this->purifier);
        $form->sanitize();

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
        $data['last_name'] = $lastName;
        $form = new \OpenCFP\Form\SignupForm($data, $this->purifier);
        $form->sanitize();

        $this->assertEquals(
            $expectedResponse,
            $form->validateLastName(),
            'Did not validate last name as expected'
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
        );
    }

    /**
     * Test that verifies that our wrapper method for validating all
     * fields works correctly
     *
     * @test
     * @param array $data
     * @param boolean $expectedResponse
     * @dataProvider validateAllProvider
     */
    public function validateAllWorksCorrectly($data, $expectedResponse)
    {
        $form = new \OpenCFP\Form\SignupForm($data, $this->purifier);
        $this->assertEquals(
            $expectedResponse,
            $form->validateAll(),
            "All submitted data did not validate as expected"
        );
    }

    /**
     * Data provider method for validateAllWOrksCorrectly
     *
     * @return array
     */
    public function validateAllProvider()
    {
        $baseData = array(
            'email' => 'test@domain.com',
            'password' => 'xxxxxx',
            'password2' => 'xxxxxx',
            'first_name' => 'Testy',
            'last_name' => 'McTesterton'
        );
        $baseDataWithSpeakerInfo = $baseData;
        $baseDataWithSpeakerInfo['speaker_info'] = "Testing speaker info data";

        return array(
            array($baseData, true),
            array($baseDataWithSpeakerInfo, true),
        );
    }

    /**
     * Test that speaker info is validated correctly
     *
     * @test
     * @param string $speakerInfo
     * @param boolean $expectedResponse
     * @dataProvider speakerTextProvider
     */
    public function speakerInfoValidatedCorrectly($speakerInfo, $expectedResponse)
    {
        $data['speaker_info'] = $speakerInfo;
        $form = new \OpenCFP\Form\SignupForm($data, $this->purifier);
        $form->sanitize();

        $this->assertEquals(
            $expectedResponse,
            $form->validateSpeakerInfo(),
            "Speaker info was not validated as expected"
        );
    }

    /**
     * Test that speaker info is validated correctly
     *
     * @test
     * @param string $speakerInfo
     * @param boolean $expectedResponse
     * @dataProvider speakerTextProvider
     */
    public function speakerBioValidatedCorrectly($speakerBio, $expectedResponse)
    {
        $data['speaker_bio'] = $speakerBio;
        $form = new \OpenCFP\Form\SignupForm($data, $this->purifier);
        $form->sanitize();
        $this->assertEquals(
            $expectedResponse,
            $form->validateSpeakerBio(),
            "Speaker bio was not validated as expected"
        );
    }

    /**
      * Data provider for speakerInfoValidatedCorrectly
      *
      * @return array
      */
    public function speakerTextProvider()
    {
        return array(
            array('Speaker text that can go in multiple places', true),
            array(null, false),
        );
    }

    /**
     * Test that we get back some sanitized data
     *
     * @test
     * @param array $inputData
     * @param array $expectedData
     * @dataProvider sanitizationProvider
     */
    public function dataGetsSanitizedCorrectly($inputData, $expectedData)
    {
        $form = new \OpenCFP\Form\SignupForm($inputData, $this->purifier);
        $form->sanitize();
        $sanitizedData = $form->getSanitizedData();
        $this->assertEquals(
            $expectedData,
            $sanitizedData,
            "Data was not sanitized properly"
        );
    }

    /**
     * Data provicer for dataGetsReturnedCorrectlySanitized
     *
     * @return array
     */
    public function sanitizationProvider()
    {
        $badDataIn = array(
            'email' => 'test@domain.com',
            'password' => 'xxxxxx',
            'password2' => 'xxxxxx',
            'first_name' => 'Testy',
            'last_name' => "<script>alert('XSS')</script>"
        );

        $badDataOut = array(
            'email' => 'test@domain.com',
            'password' => 'xxxxxx',
            'password2' => 'xxxxxx',
            'first_name' => 'Testy',
            'last_name' => ""
        );

        $goodDataIn = array(
            'email' => 'test@domain.com',
            'password' => 'xxxxxx',
            'password2' => 'xxxxxx',
            'first_name' => 'Testy',
            'last_name' => "McTesterton"
        );

        $goodDataOut = $goodDataIn;

        $badSpeakerInfoIn = array(
            'email' => 'test@domain.com',
            'password' => 'xxxxxx',
            'password2' => 'xxxxxx',
            'first_name' => 'Testy',
            'last_name' => "McTesterton",
            'speaker_info' => "<a href=\"http://lolcoin.com/redeem\">Speaker bio</a>"
        );

        $badSpeakerInfoOut = array(
            'email' => 'test@domain.com',
            'password' => 'xxxxxx',
            'password2' => 'xxxxxx',
            'first_name' => 'Testy',
            'last_name' => "McTesterton",
            'speaker_info' => "<a href=\"http://lolcoin.com/redeem\">Speaker bio</a>"
        );

        $goodSpeakerInfoIn = array(
            'email' => 'test@domain.com',
            'password' => 'xxxxxx',
            'password2' => 'xxxxxx',
            'first_name' => 'Testy',
            'last_name' => "McTesterton",
            'speaker_info' => "Find my bio at http://littlehart.net"
        );

        $goodSpeakerInfoOut = $goodSpeakerInfoIn;

        return array(
            array($badDataIn, $badDataOut),
            array($goodDataIn, $goodDataOut),
            array($badSpeakerInfoIn, $badSpeakerInfoOut),
            array($goodSpeakerInfoIn, $goodSpeakerInfoOut)
        );
    }
}
