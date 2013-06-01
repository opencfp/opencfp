<?php

use \Mockery as m;

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
     * Validation should fail if we don't have an email address in the data
     * and try to validate it
     *
     * @test
     */
    public function emailValidationShouldFailWithoutEmail()
    {
        $data = array();
        $form = new \OpenCFP\SignupForm($data);
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
     * Test that password that match and are of the proper length pass validation
     * and sanitization
     * 
     * @test
     */
    public function properPasswordsPassValidationAndSanitization()
    {
        $data = array(
            'password' => 'acceptable',
            'password2' => 'acceptable'
        );
        $form = new \OpenCFP\SignupForm($data);

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

        $form = new \OpenCFP\SignupForm($data);
        $testResponse = $form->validatePasswords();

        $this->assertEquals($expectedResponse, $testResponse);
        $this->assertContains(
            $expectedMessage,
            $form->errorMessages,
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
            array('foo', 'foo', "The submitted password must be at least 5 characters", false),
            array('bar', 'foo', "The submitted passwords do not match", false),
            array(null, null, "Missing passwords", false),
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
        $data['last_name'] = $lastName;
        $form = new \OpenCFP\SignupForm($data);

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
            array("<script>alert('XSS')</script>", false)
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
        $form = new \OpenCFP\SignupForm($data);
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
        $baseDataWithBadContent = $baseData;
        $baseDataWithBadContent['speaker_info'] = "<script>alert('LOL')</script>";

        return array(
            array($baseData, true),
            array($baseDataWithSpeakerInfo, true),
            array($baseDataWithBadContent, false)
        );
    }

    /**
     * Test that speaker info is validated correctly
     *
     * @test
     * @param string $speakerInfo
     * @param boolean $expectedResponse
     * @dataProvider speakerInfoProvider
     */
    public function speakerInfoValidatedCorrectly($speakerInfo, $expectedResponse)
    {
        $data['speaker_info'] = $speakerInfo;
        $form = new \OpenCFP\SignupForm($data);

        $this->assertEquals(
            $expectedResponse,
            $form->validateSpeakerInfo(),
            "Speaker info was not validated as expected"
        );
    }
   
    /**
      * Data provider for speakerInfoValidatedCorrectly
      *
      * @return array
      */ 
    public function speakerInfoProvider()
    {
        return array(
            array('Speaker info', true),
            array(null, false),
            array("<script>alert('LOL')</script>", false)
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
        $form = new \OpenCFP\SignupForm($inputData);
        $sanitizedData = $form->sanitize();
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

    public function testSendActivationEmail()
    {
        $activationCode = '6788ab52-8171-4190-83e7-12d4dc51baac';
        $inputData = array(
            'email' => 'test@domain.com',
            'password' => 'xxxxxx',
            'password2' => 'xxxxxx',
            'first_name' => 'Testy',
            'last_name' => "McTesterton"
        );
        $form = new \OpenCFP\SignupForm($inputData);

        $transport = m::mock();
        $transport->
            shouldReceive('setPort')->
            with(25);
        $transport->
            shouldReceive('setHost')->
            with('127.0.0.1');

        $mailer = m::mock();
        $mailer->
            shouldReceive('send')->
            withAnyArgs();

        $message = m::mock();
        $message->
            shouldReceive('setTo')->
            with(
                $inputData['email'],
                $inputData['first_name'] . ' ' . $inputData['last_name']
            )->
            andReturn(m::self());
        $message->
            shouldReceive('setFrom')->
            withAnyArgs()->
            andReturn(m::self());
        $message->
            shouldReceive('setSubject')->
            with($inputData['first_name'] . ', please confirm your account')->
            andReturn(m::self());
        $message->
            shouldReceive('setBody')->
            with('/' . $activationCode . '/')->
            andReturn(m::self());
        $message->
            shouldReceive('addPart')->
            with(
                '/' . $activationCode . '/',
                'text/html'
            )->
            andReturn(m::self());

        $user = m::mock();
        $user->
            shouldReceive('getActivationCode')->
            withNoArgs()->
            andReturn($activationCode);

        $smtp = array(
            'smtp.port' => 25,
            'smtp.host' => 'localhost',
            'smtp.user' => 'test',
            'smtp.password' => 'password'
        );

        $loader = new Twig_Loader_Filesystem('../templates');
        $twig = new Twig_Environment($loader);

        $form->sendActivationMessage(
            $user,
            $smtp,
            $twig,
            $transport,
            $mailer,
            $message
        );
    }

}
