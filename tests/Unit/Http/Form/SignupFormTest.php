<?php

declare(strict_types=1);

/**
 * Copyright (c) 2013-2019 OpenCFP
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @see https://github.com/opencfp/opencfp
 */

namespace OpenCFP\Test\Unit\Http\Form;

use Mockery as m;
use OpenCFP\Http\Form\SignupForm;
use Symfony\Component\HttpFoundation;

final class SignupFormTest extends \PHPUnit\Framework\TestCase
{
    private $purifier;

    protected function setUp()
    {
        $this->purifier = new \HTMLPurifier();
    }

    /**
     * @test
     */
    public function formRejectsTransportationRequestsWithNoAirportCode()
    {
        $form = new SignupForm(['transportation' => 1, 'airport' => ''], $this->purifier);

        $form->validateTransportationRequests();

        $this->assertTrue($form->hasErrors());
        $this->assertSame(SignupForm::MUST_PROVIDE_AIRPORT_CODE_ERROR, $form->getErrorMessages()[0]);
    }

    /**
     * @test
     */
    public function formRejectsValidationOnInvalidSpeakerPhoto()
    {
        // Mock speaker photo.
        $photo = m::mock(HttpFoundation\File\UploadedFile::class);
        $photo->shouldReceive('isValid')->andReturn(false);
        $photo->shouldReceive('getErrorMessage')->andReturn('stubbed error message');

        $form = new SignupForm(['speaker_photo' => $photo], $this->purifier);

        $form->validateSpeakerPhoto();

        $this->assertTrue($form->hasErrors());
        $this->assertContains('stubbed error message', $form->getErrorMessages()[0]);
    }

    /**
     * Test that form object rejects validation when we don't have
     * enough fields
     *
     * @test
     */
    public function formRejectsValidationOnMissingFields()
    {
        $data = [
            'email'       => 'test@domain.com',
            'notrequired' => 'test',
        ];
        $form     = new \OpenCFP\Http\Form\SignupForm($data, $this->purifier);
        $response = $form->hasRequiredFields();
        $this->assertFalse($response);
    }

    /**
     * Verify that emails are being validated correctly
     *
     * @test
     *
     * @param string $email
     * @param bool   $expectedResponse
     * @dataProvider emailProvider
     */
    public function emailsAreBeingValidatedCorrectly($email, $expectedResponse)
    {
        $data = ['email' => $email];
        $form = new \OpenCFP\Http\Form\SignupForm($data, $this->purifier);
        $this->assertSame(
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
        $data = [];
        $form = new \OpenCFP\Http\Form\SignupForm($data, $this->purifier);
        $this->assertFalse(
            $form->validateEmail(),
            'Validating empty email did not fail'
        );
    }

    /**
     * Data provider for emailsAreBeingValidatedCorrectly
     *
     * @return array
     */
    public function emailProvider(): array
    {
        return [
            ['test', false],
            ['test@domain.com', true],
            ['', false],
            ['test@domain', false],
            ['test+tricky@domain.com', true],
        ];
    }

    /**
     * Data provider for properPasswordsPassValidationAndSanitization
     *
     * @return array
     */
    public function properPasswordValidator(): array
    {
        return [
            ['acceptable'],
            ['testing123'],
            ['{^secur3'],
            ['invalidChars&*$'],
        ];
    }

    /**
     * Test that password that match and are of the proper length pass validation
     * and sanitization
     *
     * @test
     *
     * @param string $passwd
     * @dataProvider properPasswordValidator
     */
    public function properPasswordsPassValidationAndSanitization($passwd)
    {
        $data = [
            'password'  => $passwd,
            'password2' => $passwd,
        ];
        $form = new \OpenCFP\Http\Form\SignupForm($data, $this->purifier);
        $form->sanitize();

        $this->assertTrue(
            $form->validatePasswords(),
            'Valid passwords did not survive validation and sanitization'
        );
    }

    /**
     * Test that bad passwords are being correctly matched and sanitized
     *
     * @test
     *
     * @param string $passwd
     * @param string $passwd2
     * @param string $expectedMessage
     * @param bool   $expectedResponse
     * @dataProvider badPasswordProvider
     */
    public function badPasswordsAreBeingCorrectlyDetected($passwd, $passwd2, $expectedMessage, $expectedResponse)
    {
        $data = [
            'password'  => $passwd,
            'password2' => $passwd2,
        ];

        $form = new \OpenCFP\Http\Form\SignupForm($data, $this->purifier);
        $form->sanitize();
        $testResponse = $form->validatePasswords();

        $this->assertSame($expectedResponse, $testResponse);
        $this->assertContains(
            $expectedMessage,
            $form->getErrorMessages(),
            'Did not get expected error message'
        );
    }

    /**
     * Data provider for passwordsAreBeingCorrectlyMatched
     *
     * @return array
     */
    public function badPasswordProvider(): array
    {
        return [
            ['foo', 'foo', 'The submitted password must be at least 5 characters long', false],
            ['bar', 'foo', 'The submitted passwords do not match', false],
            [null, null, 'Missing passwords', false],
            ['password with spaces', 'password with spaces', 'The submitted password contains invalid characters', false],
        ];
    }

    /**
     * Test that the firstName is being validated correctly
     *
     * @test
     *
     * @param string $firstName
     * @param bool   $expectedResponse
     * @dataProvider firstNameProvider
     */
    public function firstNameIsValidatedCorrectly($firstName, $expectedResponse)
    {
        $data['first_name'] = $firstName;
        $form               = new \OpenCFP\Http\Form\SignupForm($data, $this->purifier);
        $form->sanitize();

        $this->assertSame(
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
    public function firstNameProvider(): array
    {
        $longName = '';

        for ($x = 1; $x <= 256; ++$x) {
            $longName .= 'X';
        }

        return [
            ['Chris', true],
            [null, false],
            ['', false],
            [false, false],
            [$longName, false],
        ];
    }

    /**
     * Test that the lastName is being validated correctly
     *
     * @test
     *
     * @param string $lastName
     * @param bool   $expectedResponse
     * @dataProvider lastNameProvider
     */
    public function lastNameIsValidatedCorrectly($lastName, $expectedResponse)
    {
        $data['last_name'] = $lastName;
        $form              = new \OpenCFP\Http\Form\SignupForm($data, $this->purifier);
        $form->sanitize();

        $this->assertSame(
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
    public function lastNameProvider(): array
    {
        $longName = '';

        for ($x = 1; $x <= 256; ++$x) {
            $longName .= 'X';
        }

        return [
            ['Chris', true],
            [null, false],
            ['', false],
            [false, false],
            [$longName, false],
        ];
    }

    /**
     * @test
     *
     * @param string $joindin_username
     * @param bool   $expectedResponse
     * @dataProvider joindInUsernameProvider
     */
    public function joindInUsernameIsValidatedCorrectly($joindin_username, $expectedResponse)
    {
        $data['joindin_username'] = $joindin_username;

        $form = new \OpenCFP\Http\Form\SignupForm($data, $this->purifier);
        $form->sanitize();

        $this->assertSame(
            $expectedResponse,
            $form->validateJoindInUsername(),
            'Did not validate joind.in username as expected'
        );
    }

    public function joindInUsernameProvider(): array
    {
        return [
            [null, true],
            [false, true],
            ['', true],
            ['abc123', true],
            ['do re mi', false],
            ['_FirstLast', true],
            ['first-last', true],
            ['first@last', false],
            ['first#last', false],
            ['first.last', true],
            [\str_repeat('X', 100), true],
            [\str_repeat('X', 101), false],
        ];
    }

    /**
     * @test
     *
     * @param string $url
     * @param bool   $expectedResponse
     * @dataProvider urlProvider
     */
    public function urlIsValidatedCorrectly($url, $expectedResponse)
    {
        $data['url'] = $url;
        $form        = new \OpenCFP\Http\Form\SignupForm($data, $this->purifier);
        $form->sanitize();

        $this->assertSame(
            $expectedResponse,
            $form->validateUrl(),
            'Did not validate URL as expected'
        );
    }

    public function urlProvider(): array
    {
        return [
            [null, true],
            [false, true],
            ['', true],
            ['http://example.net', true],
            ['example', false],
            ['http://example.com/do re mi', false],
            ['http://example.com/_FirstLast', true],
            ['http://example.com/first-last', true],
            ['https://', false],
            ['$19.95 plus shipping and handling', false],
        ];
    }

    /**
     * Test that verifies that our wrapper method for validating all
     * fields works correctly
     *
     * @test
     *
     * @param array $data
     * @param bool  $expectedResponse
     * @dataProvider validateAllProvider
     */
    public function validateAllWorksCorrectly($data, $expectedResponse)
    {
        $form = new \OpenCFP\Http\Form\SignupForm($data, $this->purifier);
        $this->assertSame(
            $expectedResponse,
            $form->validateAll(),
            'All submitted data did not validate as expected'
        );
    }

    /**
     * Data provider method for validateAllWOrksCorrectly
     *
     * @return array
     */
    public function validateAllProvider(): array
    {
        $baseData = [
            'email'      => 'test@domain.com',
            'password'   => 'xxxxxx',
            'password2'  => 'xxxxxx',
            'first_name' => 'Tésty',
            'last_name'  => 'McTestèrton',
            'url'        => 'https://joind.in/user/abc123',
        ];
        $baseDataWithSpeakerInfo                 = $baseData;
        $baseDataWithSpeakerInfo['speaker_info'] = 'Testing speaker info data';

        return [
            [$baseData, true],
            [$baseDataWithSpeakerInfo, true],
        ];
    }

    /**
     * Test that speaker info is validated correctly
     *
     * @test
     *
     * @param string $speakerInfo
     * @param bool   $expectedResponse
     * @dataProvider speakerTextProvider
     */
    public function speakerInfoValidatedCorrectly($speakerInfo, $expectedResponse)
    {
        $data['speaker_info'] = $speakerInfo;
        $form                 = new \OpenCFP\Http\Form\SignupForm($data, $this->purifier);
        $form->sanitize();

        $this->assertSame(
            $expectedResponse,
            $form->validateSpeakerInfo(),
            'Speaker info was not validated as expected'
        );
    }

    /**
     * Test that speaker info is validated correctly
     *
     * @test
     *
     * @param string $speakerBio
     * @param bool   $expectedResponse
     * @dataProvider speakerTextProvider
     */
    public function speakerBioValidatedCorrectly($speakerBio, $expectedResponse)
    {
        $data['speaker_bio'] = $speakerBio;
        $form                = new \OpenCFP\Http\Form\SignupForm($data, $this->purifier);
        $form->sanitize();
        $this->assertSame(
            $expectedResponse,
            $form->validateSpeakerBio(),
            'Speaker bio was not validated as expected'
        );
    }

    /**
     * Data provider for speakerInfoValidatedCorrectly
     *
     * @return array
     */
    public function speakerTextProvider(): array
    {
        return [
            ['Speaker text that can go in multiple places', true],
            [null, false],
        ];
    }

    /**
     * Test that we get back some sanitized data
     *
     * @test
     *
     * @param array $inputData
     * @param array $expectedData
     * @dataProvider sanitizationProvider
     */
    public function dataGetsSanitizedCorrectly($inputData, $expectedData)
    {
        $form = new \OpenCFP\Http\Form\SignupForm($inputData, $this->purifier);
        $form->sanitize();
        $sanitizedData = $form->getCleanData();
        $this->assertSame(
            $expectedData,
            $sanitizedData,
            'Data was not sanitized properly'
        );
    }

    /**
     * Data provider for dataGetsReturnedCorrectlySanitized
     *
     * @return array
     */
    public function sanitizationProvider(): array
    {
        $badDataIn = [
            'email'      => 'test@domain.com',
            'password'   => 'xxxxxx',
            'password2'  => 'xxxxxx',
            'first_name' => 'Testy',
            'last_name'  => "<script>alert('XSS')</script>",
        ];

        $badDataOut = [
            'email'      => 'test@domain.com',
            'password'   => 'xxxxxx',
            'password2'  => 'xxxxxx',
            'first_name' => 'Testy',
            'last_name'  => '',
        ];

        $goodDataIn = [
            'email'      => 'test@domain.com',
            'password'   => 'xxxxxx',
            'password2'  => 'xxxxxx',
            'first_name' => 'Testy',
            'last_name'  => 'McTesterton',
        ];

        $goodDataOut = $goodDataIn;

        $badSpeakerInfoIn = [
            'email'        => 'test@domain.com',
            'password'     => 'xxxxxx',
            'password2'    => 'xxxxxx',
            'first_name'   => 'Testy',
            'last_name'    => 'McTesterton',
            'speaker_info' => '<a href="http://lolcoin.com/redeem">Speaker bio</a>',
        ];

        $badSpeakerInfoOut = [
            'email'        => 'test@domain.com',
            'password'     => 'xxxxxx',
            'password2'    => 'xxxxxx',
            'first_name'   => 'Testy',
            'last_name'    => 'McTesterton',
            'speaker_info' => '<a href="http://lolcoin.com/redeem">Speaker bio</a>',
        ];

        $goodSpeakerInfoIn = [
            'email'        => 'test@domain.com',
            'password'     => 'xxxxxx',
            'password2'    => 'xxxxxx',
            'first_name'   => 'Testy',
            'last_name'    => 'McTesterton',
            'speaker_info' => 'Find my bio at http://littlehart.net',
        ];

        $goodSpeakerInfoOut = $goodSpeakerInfoIn;

        return [
            [$badDataIn, $badDataOut],
            [$goodDataIn, $goodDataOut],
            [$badSpeakerInfoIn, $badSpeakerInfoOut],
            [$goodSpeakerInfoIn, $goodSpeakerInfoOut],
        ];
    }
}
