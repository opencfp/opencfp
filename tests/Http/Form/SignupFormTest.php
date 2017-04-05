<?php

namespace OpenCFP\Test\Http\Form;

use Mockery as m;
use OpenCFP\Http\Form\SignupForm;

class SignupFormTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var array
     */
    protected $taintedData = [];

    /**
     * @var Mockery|\HTMLPurifier
     */
    protected $purifierDouble;

    /**
     * @var SignupForm
     */
    private $signupForm;

    protected function setUp()
    {
        parent::setUp();

        $this->taintedData = $this->getSampleData();

        $this->purifierDouble = m::mock('HTMLPurifier');

        $this->signupForm = new SignupForm($this->taintedData, $this->purifierDouble);
    }

    public function tearDown()
    {
        parent::tearDown();
        m::close();
    }

    /**
     * Returns an array of data in order to conduct different tests on this form object.
     *
     * @param string $type
     * @return array
     */
    protected function getSampleData(string $type = 'generic') : array
    {
        $sampleData = [
            'email' => 'you@domain.org',
            'password' => 'test1',
            'password2' => 'test1',
            'first_name' => 'John',
            'last_name' => 'Doe',
            'company' => 'ACME',
            'twitter' => '@twitter',
            'speaker_info' => 'Some info.',
            'speaker_bio' => 'Who is this guy?',
            'transportation' => true,
            'hotel' => true,
            'speaker_photo' => '',
            'agree_coc' => 'agreed',
        ];

        if ($type === 'clean') {
            $sampleData = array_merge($sampleData, ['twitter' => 'twitter']);
        } elseif ($type === 'incomplete') {
            unset($sampleData['email']);
        }

        return $sampleData;
    }

    /**
     * Test that form object's properties are correctly populated
     * on instantiation.
     *
     * @test
     */
    public function formPropertiesAreCorrectlyPopulatedOnInstantiation()
    {
        $this->assertEquals($this->taintedData, $this->signupForm->getTaintedData());
    }

    /**
     * Test that the form object's properties are correctly populated.
     *
     * @test
     */
    public function formPropertiesAreCorrectlyPopulatedWhenManuallyInvoked()
    {
        $this->assertEquals($this->taintedData, $this->signupForm->getTaintedData());

        $cleanData = $this->getSampleData('clean');

        $this->purifierDouble
            ->shouldReceive('purify')
            ->andReturnValues($cleanData);

        $this->signupForm->sanitize();
        $this->assertFalse($this->signupForm->hasErrors());
        $this->assertEquals($cleanData, $this->signupForm->getCleanData());

        $newData = [
            'email' => 'newyou@domain.org',
            'password' => 'newtest1',
            'password2' => 'newtest1',
            'first_name' => 'NewJohn',
            'last_name' => 'NewDoe',
            'company' => 'NewACME',
            'twitter' => '@newtwitter',
            'speaker_info' => 'NEW - Some info.',
            'speaker_bio' => 'NEW - Who is this guy?',
            'transportation' => false,
            'hotel' => false,
            'speaker_photo' => '/tmp/somefolder/newimage.png',
            'agree_coc' => 'agreed',
        ];

        $this->signupForm->populate($newData);

        $this->assertEquals($newData, $this->signupForm->getTaintedData());
        $this->assertTrue(empty($this->signupForm->getCleanData()));
    }

    /**
     * Test that the form can filter the tainted data.
     *
     * @test
     */
    public function formCanFilterTheTaintedDataAndReturnTheCleanData()
    {
        $cleanData = $this->getSampleData('clean');

        $this->purifierDouble
            ->shouldReceive('purify')
            ->andReturnValues($cleanData);

        $this->assertNull($this->signupForm->getCleanData());

        $this->signupForm->sanitize();

        $this->assertEquals(
            $cleanData,
            $this->signupForm->getCleanData()
        );

        $this->assertSame(
            ['email' => 'you@domain.org'],
            $this->signupForm->getCleanData(['email'])
        );
    }

    /**
     * Test that the form object's properties are correctly updated.
     *
     * @test
     */
    public function formPropertiesAreCorrectlyUpdated()
    {
        $newData = [
            'email' => 'you@domain.org',
            'other' => 'Who knows what else!',
        ];

        $newTaintedData = array_merge($this->taintedData, $newData);

        $this->signupForm->update($newTaintedData);

        $this->assertEquals($newTaintedData, $this->signupForm->getTaintedData());
    }

    /**
     * Test that the form can correctly check if it has all required fields.
     *
     * @test
     */
    public function formCanCorrectlyCheckThatItHasAllRequiredFields()
    {
        $this->assertTrue($this->signupForm->hasRequiredFields());

        $incompleteData = $this->getSampleData('incomplete');

        $this->signupForm = new SignupForm($incompleteData, $this->purifierDouble);

        $this->assertFalse($this->signupForm->hasRequiredFields());
    }

    /**
     * Test that the form can return a single field value.
     *
     * @test
     */
    public function formCanReturnSingleFieldValueOrDefaultValueOrNullIfNotDefined()
    {
        $this->assertSame(
            'you@domain.org',
            $this->signupForm->getTaintedField('email')
        );

        $this->assertSame(
            'some default value',
            $this->signupForm->getTaintedField('other', 'some default value')
        );

        $this->assertSame('you@domain.org', $this->signupForm->getTaintedField('email'));

        $this->assertNull($this->signupForm->getTaintedField('other'));
    }

    /**
     * Test that the form can return a single option value.
     *
     * @test
     */
    public function formCanReturnSingleOptionValueOrDefaultValueOrNullIfNotDefined()
    {
        $this->assertNull($this->signupForm->getOption('other'));

        $this->assertSame(
            'some default value',
            $this->signupForm->getOption('other', 'some default value')
        );

        $this->signupForm = new SignupForm(
            $this->taintedData,
            $this->purifierDouble,
            ['other' => 'test']
        );

        $this->assertSame(
            'test',
            $this->signupForm->getOption('other')
        );
    }

    /**
     * Test that the form can correctly validate all fields at once.
     *
     * @test
     */
    public function formCanCorrectlyValidateAllFieldsAtOnce()
    {
        $cleanData = $this->getSampleData('clean');

        $this->purifierDouble
            ->shouldReceive('purify')
            ->andReturnValues($cleanData);

        $eloquentDouble = m::mock('stdClass');
        $eloquentDouble
            ->shouldReceive('isValid')
            ->andReturn(true);
        $eloquentDouble
            ->shouldReceive('getClientSize')
            ->andReturn(1048576);
        $eloquentDouble
            ->shouldReceive('getMimeType')
            ->andReturn('image/png');

        $cleanData = array_merge(
            $cleanData,
            ['speaker_photo' => $eloquentDouble]
        );

        $this->signupForm = new SignupForm($cleanData, $this->purifierDouble);

        $this->assertFalse($this->signupForm->hasErrors());
        $this->assertTrue($this->signupForm->validateAll());
        $this->assertFalse($this->signupForm->hasErrors());
    }

    /**
     * Test that the form can correctly validate all fields at once
     * when one field is invalid.
     *
     * @test
     */
    public function formCanCorrectlyValidateAllFieldsAtOnceWhenOneFieldIsInvalid()
    {
        $cleanData = $this->getSampleData('clean');

        $eloquentDouble = m::mock('stdClass');
        $eloquentDouble
            ->shouldReceive('isValid')
            ->andReturn(true);
        $eloquentDouble
            ->shouldReceive('getClientSize')
            ->andReturn(1048576);
        $eloquentDouble
            ->shouldReceive('getMimeType')
            ->andReturn('image/png');

        $cleanData = array_merge(
            $cleanData,
            [
                'speaker_photo' => $eloquentDouble,
                'agree_coc' => 'not agreed',
            ]
        );

        $this->purifierDouble
            ->shouldReceive('purify')
            ->andReturnValues($cleanData);

        $this->signupForm = new SignupForm($cleanData, $this->purifierDouble);

        $this->assertFalse($this->signupForm->hasErrors());
        $this->assertFalse($this->signupForm->validateAll());
        $this->assertTrue($this->signupForm->hasErrors());
        $this->assertSame(
            ['You must agree to abide by our code of conduct in order to submit'],
            $this->signupForm->getErrorMessages()
        );
    }

    /**
     * Test that the form can correctly validate the speaker photo field
     * if the field is not set.
     *
     * @test
     */
    public function formCanCorrectlyValidateNotSetSpeakerPhotoField()
    {
        unset($this->taintedData['speaker_photo']);
        $this->signupForm = new SignupForm($this->taintedData, $this->purifierDouble);

        $this->assertFalse($this->signupForm->hasErrors());
        $this->assertTrue($this->signupForm->validateSpeakerPhoto());
        $this->assertFalse($this->signupForm->hasErrors());
    }

    /**
     * Test that the form can correctly validate the speaker photo field.
     *
     * @test
     */
    public function formCanCorrectlyValidateSpeakerPhotoField()
    {
        $eloquentDouble = m::mock('stdClass');
        $eloquentDouble
            ->shouldReceive('isValid')
            ->andReturn(true);
        $eloquentDouble
            ->shouldReceive('getClientSize')
            ->andReturn(1048576);
        $eloquentDouble
            ->shouldReceive('getMimeType')
            ->andReturn('image/png');

        $this->taintedData = array_merge(
            $this->taintedData,
            ['speaker_photo' => $eloquentDouble]
        );

        $this->signupForm = new SignupForm($this->taintedData, $this->purifierDouble);

        $this->assertFalse($this->signupForm->hasErrors());
        $this->assertTrue($this->signupForm->validateSpeakerPhoto());
        $this->assertFalse($this->signupForm->hasErrors());
    }

    /**
     * Test that the form can correctly validate the speaker photo field
     * if it is not valid.
     *
     * @test
     */
    public function formCanCorrectlyValidateSpeakerPhotoFieldNotValid()
    {
        $eloquentDouble = m::mock('stdClass');
        $eloquentDouble
            ->shouldReceive('isValid')
            ->andReturn(false);
        $eloquentDouble
            ->shouldReceive('getClientSize')
            ->andReturn(1048576);
        $eloquentDouble
            ->shouldReceive('getMimeType')
            ->andReturn('image/png');
        $eloquentDouble
            ->shouldReceive('getErrorMessage')
            ->andReturn('Upload failed');

        $this->taintedData = array_merge(
            $this->taintedData,
            ['speaker_photo' => $eloquentDouble]
        );

        $this->signupForm = new SignupForm($this->taintedData, $this->purifierDouble);

        $this->assertFalse($this->signupForm->hasErrors());
        $this->assertFalse($this->signupForm->validateSpeakerPhoto());
        $this->assertTrue($this->signupForm->hasErrors());
        $this->assertSame(
            ['Upload failed'],
            $this->signupForm->getErrorMessages()
        );
    }

    /**
     * Test that the form can correctly validate the speaker photo field
     * if the file exceeds 5MB.
     *
     * @test
     */
    public function formCanCorrectlyValidateSpeakerPhotoFieldExceeds5MB()
    {
        $eloquentDouble = m::mock('stdClass');
        $eloquentDouble
            ->shouldReceive('isValid')
            ->andReturn(true);
        $eloquentDouble
            ->shouldReceive('getClientSize')
            ->andReturn(5 * 1048576 + 1);
        $eloquentDouble
            ->shouldReceive('getMimeType')
            ->andReturn('image/png');

        $this->taintedData = array_merge(
            $this->taintedData,
            ['speaker_photo' => $eloquentDouble]
        );

        $this->signupForm = new SignupForm($this->taintedData, $this->purifierDouble);

        $this->assertFalse($this->signupForm->hasErrors());
        $this->assertFalse($this->signupForm->validateSpeakerPhoto());
        $this->assertTrue($this->signupForm->hasErrors());
        $this->assertSame(
            ['Speaker photo can not be larger than 5MB'],
            $this->signupForm->getErrorMessages()
        );
    }

    /**
     * Test that the form can correctly validate the speaker photo field
     * if the file is of the wrong mime type.
     *
     * @test
     */
    public function formCanCorrectlyValidateSpeakerPhotoFieldWrongMimeType()
    {
        $eloquentDouble = m::mock('stdClass');
        $eloquentDouble
            ->shouldReceive('isValid')
            ->andReturn(true);
        $eloquentDouble
            ->shouldReceive('getClientSize')
            ->andReturn(1048576);
        $eloquentDouble
            ->shouldReceive('getMimeType')
            ->andReturn('image/txt');

        $this->taintedData = array_merge(
            $this->taintedData,
            ['speaker_photo' => $eloquentDouble]
        );

        $this->signupForm = new SignupForm($this->taintedData, $this->purifierDouble);

        $this->assertFalse($this->signupForm->hasErrors());
        $this->assertFalse($this->signupForm->validateSpeakerPhoto());
        $this->assertTrue($this->signupForm->hasErrors());
        $this->assertSame(
            ['Speaker photo must be a jpg or png'],
            $this->signupForm->getErrorMessages()
        );
    }

    /**
     * Test that the form can correctly validate an email address.
     *
     * @test
     */
    public function formCanCorrectlyValidateEmailAddressAndReturnsCorrespondingErrorMessages()
    {
        $this->assertFalse($this->signupForm->hasErrors());
        $this->assertTrue($this->signupForm->validateEmail());
        $this->assertFalse($this->signupForm->hasErrors());

        $incompleteData = $this->getSampleData('incomplete');

        $this->signupForm = new SignupForm($incompleteData, $this->purifierDouble);
        $this->assertFalse($this->signupForm->hasErrors());
        $this->assertFalse($this->signupForm->validateEmail());
        $this->assertTrue($this->signupForm->hasErrors());
        $this->assertSame(['Missing email'], $this->signupForm->getErrorMessages());

        $this->signupForm = new SignupForm($incompleteData, $this->purifierDouble);
        $this->assertFalse($this->signupForm->hasErrors());
        $this->signupForm->update(['email' => 'bad.email.address.format']);
        $this->assertFalse($this->signupForm->validateEmail());
        $this->assertTrue($this->signupForm->hasErrors());
        $this->assertSame(
            ['Invalid email address format'],
            $this->signupForm->getErrorMessages()
        );
    }

    /**
     * Test that the form can correctly validate the password field.
     *
     * @test
     */
    public function formCanCorrectlyValidatePasswordFieldAndReturnCorrespondingErrorMessages()
    {
        $this->purifierDouble
            ->shouldReceive('purify');

        $this->signupForm->sanitize();
        $this->assertFalse($this->signupForm->hasErrors());
        $this->assertTrue($this->signupForm->validatePasswords());
        $this->assertFalse($this->signupForm->hasErrors());
    }

    /**
     * Test that the form can correctly validate the password field
     * if the password fields are empty.
     *
     * @test
     */
    public function formCanCorrectlyValidateEmptyPasswordFieldAndReturnCorrespondingErrorMessages()
    {
        $this->purifierDouble
            ->shouldReceive('purify');

        $this->signupForm->update(['password' => '']);
        $this->signupForm->sanitize();
        $this->assertFalse($this->signupForm->hasErrors());
        $this->assertFalse($this->signupForm->validatePasswords());
        $this->assertTrue($this->signupForm->hasErrors());
        $this->assertSame(
            ['Missing passwords'],
            $this->signupForm->getErrorMessages()
        );
    }

    /**
     * Test that the form can correctly validate the password field
     * if the password fields do not match.
     *
     * @test
     */
    public function formCanCorrectlyValidateNotMatchingPasswordFieldAndReturnCorrespondingErrorMessages()
    {
        $this->purifierDouble
            ->shouldReceive('purify');

        $this->signupForm = new SignupForm($this->taintedData, $this->purifierDouble);
        $this->signupForm->update(['password' => 'test']);
        $this->signupForm->sanitize();
        $this->assertFalse($this->signupForm->hasErrors());
        $this->assertFalse($this->signupForm->validatePasswords());
        $this->assertTrue($this->signupForm->hasErrors());
        $this->assertSame(
            ['The submitted passwords do not match'],
            $this->signupForm->getErrorMessages()
        );
    }

    /**
     * Test that the form can correctly validate the password field
     * if the submitted password is less than 5 characters long.
     *
     * @test
     */
    public function formCanCorrectlyValidateShortPasswordFieldAndReturnCorrespondingErrorMessages()
    {
        $this->purifierDouble
            ->shouldReceive('purify');

        $this->signupForm = new SignupForm($this->taintedData, $this->purifierDouble);
        $this->signupForm->update(['password' => 'test', 'password2' => 'test']);
        $this->signupForm->sanitize();
        $this->assertFalse($this->signupForm->hasErrors());
        $this->assertFalse($this->signupForm->validatePasswords());
        $this->assertTrue($this->signupForm->hasErrors());
        $this->assertSame(
            ['The submitted password must be at least 5 characters long'],
            $this->signupForm->getErrorMessages()
        );
    }

    /**
     * Test that the form can correctly validate the password field
     * when it contains an invalid character.
     *
     * @test
     */
    public function formCanCorrectlyValidateInvalidCharacterInPasswordFieldAndReturnCorrespondingErrorMessages()
    {
        $this->purifierDouble
            ->shouldReceive('purify');

        $this->signupForm = new SignupForm($this->taintedData, $this->purifierDouble);
        $this->signupForm->update(['password' => 'te st', 'password2' => 'te st']);
        $this->signupForm->sanitize();
        $this->assertFalse($this->signupForm->hasErrors());
        $this->assertFalse($this->signupForm->validatePasswords());
        $this->assertTrue($this->signupForm->hasErrors());
        $this->assertSame(
            ['The submitted password contains invalid characters'],
            $this->signupForm->getErrorMessages()
        );
    }

    /**
     * Test that the form can correctly validate the first name field.
     *
     * @test
     */
    public function formCanCorrectlyValidateFirstNameField()
    {
        $this->purifierDouble
            ->shouldReceive('purify')
            ->andReturn('John');

        $this->signupForm->sanitize();
        $this->assertFalse($this->signupForm->hasErrors());
        $this->assertTrue($this->signupForm->validateFirstName());
        $this->assertFalse($this->signupForm->hasErrors());
    }

    /**
     * Test that the form can correctly validate the first name field
     * when it is empty.
     *
     * @test
     */
    public function formCanCorrectlyValidateEmptyFirstNameFieldAndReturnCorrespondingErrorMessages()
    {
        $emptyField = '';

        $this->purifierDouble
            ->shouldReceive('purify')
            ->andReturn($emptyField);

        $this->signupForm = new SignupForm($this->taintedData, $this->purifierDouble);
        $this->signupForm->update(['first_name' => '']);
        $this->signupForm->sanitize();
        $this->assertFalse($this->signupForm->hasErrors());
        $this->assertFalse($this->signupForm->validateFirstName());
        $this->assertTrue($this->signupForm->hasErrors());
        $this->assertSame(
            ['First name cannot be blank'],
            $this->signupForm->getErrorMessages()
        );
    }

    /**
     * Test that the form can correctly validate the first name field
     * when it has more than 255 characters.
     *
     * @test
     */
    public function formCanCorrectlyValidateLongFirstNameFieldAndReturnCorrespondingErrorMessages()
    {
        $longName = str_repeat('Fonz', 64);

        $this->purifierDouble
            ->shouldReceive('purify')
            ->andReturn($longName);

        $this->signupForm = new SignupForm($this->taintedData, $this->purifierDouble);
        $this->signupForm->update(['first_name' => $longName]);
        $this->signupForm->sanitize();
        $this->assertFalse($this->signupForm->hasErrors());
        $this->assertFalse($this->signupForm->validateFirstName());
        $this->assertTrue($this->signupForm->hasErrors());
        $this->assertSame(
            ['First name cannot exceed 255 characters'],
            $this->signupForm->getErrorMessages()
        );
    }

    /**
     * Test that the form can correctly validate the first name field
     * when it contains an invalid character.
     *
     * @test
     */
    public function formCanCorrectlyValidateInvalidCharacterInFirstNameFieldAndReturnCorrespondingErrorMessages()
    {
        $this->purifierDouble
            ->shouldReceive('purify')
            ->andReturn('John');

        $this->signupForm = new SignupForm($this->taintedData, $this->purifierDouble);
        $this->signupForm->update(['first_name' => 'John&']);
        $this->signupForm->sanitize();
        $this->assertFalse($this->signupForm->hasErrors());
        $this->assertFalse($this->signupForm->validateFirstName());
        $this->assertTrue($this->signupForm->hasErrors());
        $this->assertSame(
            ['First name contains unwanted characters'],
            $this->signupForm->getErrorMessages()
        );
    }

    /**
     * Test that the form can correctly validate the last name field.
     *
     * @test
     */
    public function formCanCorrectlyValidateLastNameField()
    {
        $this->purifierDouble
            ->shouldReceive('purify')
            ->andReturn('Doe');

        $this->signupForm->sanitize();
        $this->assertFalse($this->signupForm->hasErrors());
        $this->assertTrue($this->signupForm->validateLastName());
        $this->assertFalse($this->signupForm->hasErrors());
    }

    /**
     * Test that the form can correctly validate the last name field
     * when it is empty.
     *
     * @test
     */
    public function formCanCorrectlyValidateEmptyLastNameFieldAndReturnCorrespondingErrorMessages()
    {
        $emptyField = '';

        $this->purifierDouble
            ->shouldReceive('purify')
            ->andReturn($emptyField);

        $this->signupForm = new SignupForm($this->taintedData, $this->purifierDouble);
        $this->signupForm->update(['last_name' => '']);
        $this->signupForm->sanitize();
        $this->assertFalse($this->signupForm->hasErrors());
        $this->assertFalse($this->signupForm->validateLastName());
        $this->assertTrue($this->signupForm->hasErrors());
        $this->assertSame(
            ['Last name was blank or contained unwanted characters'],
            $this->signupForm->getErrorMessages()
        );
    }

    /**
     * Test that the form can correctly validate the last name field
     * when it has more than 255 characters.
     *
     * @test
     */
    public function formCanCorrectlyValidateLongLastNameFieldAndReturnCorrespondingErrorMessages()
    {
        $longName = str_repeat('Fonz', 64);

        $this->purifierDouble
            ->shouldReceive('purify')
            ->andReturn($longName);

        $this->signupForm = new SignupForm($this->taintedData, $this->purifierDouble);
        $this->signupForm->update(['last_name' => $longName]);
        $this->signupForm->sanitize();
        $this->assertFalse($this->signupForm->hasErrors());
        $this->assertFalse($this->signupForm->validateLastName());
        $this->assertTrue($this->signupForm->hasErrors());
        $this->assertSame(
            ['Last name cannot be longer than 255 characters'],
            $this->signupForm->getErrorMessages()
        );
    }

    /**
     * Test that the form can correctly validate the last name field
     * when it contains an invalid character.
     *
     * @test
     */
    public function formCanCorrectlyValidateInvalidCharacterInLastNameFieldAndReturnCorrespondingErrorMessages()
    {
        $this->purifierDouble
            ->shouldReceive('purify')
            ->andReturn('Doe');

        $this->signupForm = new SignupForm($this->taintedData, $this->purifierDouble);
        $this->signupForm->update(['last_name' => 'Doe&']);
        $this->signupForm->sanitize();
        $this->assertFalse($this->signupForm->hasErrors());
        $this->assertFalse($this->signupForm->validateLastName());
        $this->assertTrue($this->signupForm->hasErrors());
        $this->assertSame(
            ['Last name data did not match after sanitizing'],
            $this->signupForm->getErrorMessages()
        );
    }

    /**
     * Test that the form can correctly validate the company field.
     *
     * @test
     */
    public function formCanCorrectlyValidateCompanyField()
    {
        /* @TODO Test not yet implemented. */
        $this->assertTrue($this->signupForm->validateCompany());
    }

    /**
     * Test that the form can correctly validate the Twitter field.
     *
     * @test
     */
    public function formCanCorrectlyValidateTwitterField()
    {
        /* @TODO Test not yet implemented. */
        $this->assertTrue($this->signupForm->validateTwitter());
    }

    /**
     * Test that the form can correctly validate the speaker info field.
     *
     * @test
     */
    public function formCanCorrectlyValidateSpeakerInfoField()
    {
        $this->purifierDouble
            ->shouldReceive('purify')
            ->andReturn('Some info.');

        $this->signupForm->sanitize();
        $this->assertFalse($this->signupForm->hasErrors());
        $this->assertTrue($this->signupForm->validateSpeakerInfo());
        $this->assertFalse($this->signupForm->hasErrors());
    }

    /**
     * Test that the form can correctly validate the speaker info field
     * when it is empty.
     *
     * @test
     */
    public function formCanCorrectlyValidateEmptySpeakerInfoFieldAndReturnCorrespondingErrorMessage()
    {
        $emptyField = '';

        $this->purifierDouble
            ->shouldReceive('purify')
            ->andReturn($emptyField);

        $this->signupForm->sanitize();
        $this->assertFalse($this->signupForm->hasErrors());
        $this->assertFalse($this->signupForm->validateSpeakerInfo());
        $this->assertTrue($this->signupForm->hasErrors());
        $this->assertSame(
            ['You submitted speaker info but it was empty after sanitizing'],
            $this->signupForm->getErrorMessages()
        );
    }

    /**
     * Test that the form can correctly validate the speaker bio field.
     *
     * @test
     */
    public function formCanCorrectlyValidateSpeakerBioField()
    {
        $this->purifierDouble
            ->shouldReceive('purify')
            ->andReturn('Who is this guy?');

        $this->signupForm->sanitize();
        $this->assertFalse($this->signupForm->hasErrors());
        $this->assertTrue($this->signupForm->validateSpeakerBio());
        $this->assertFalse($this->signupForm->hasErrors());
    }

    /**
     * Test that the form can correctly validate the speaker bio field
     * when it is empty.
     *
     * @test
     */
    public function formCanCorrectlyValidateEmptySpeakerBioFieldAndReturnCorrespondingErrorMessage()
    {
        $emptyField = '';

        $this->purifierDouble
            ->shouldReceive('purify')
            ->andReturn($emptyField);

        $this->signupForm->sanitize();
        $this->assertFalse($this->signupForm->hasErrors());
        $this->assertFalse($this->signupForm->validateSpeakerBio());
        $this->assertTrue($this->signupForm->hasErrors());
        $this->assertSame(
            ['You submitted speaker bio information but it was empty after sanitizing'],
            $this->signupForm->getErrorMessages()
        );
    }

    /**
     * Test that the form can correctly validate the agree coc field.
     *
     * @test
     */
    public function formCanCorrectlyValidateAgreeCocField()
    {
        $cleanData = $this->getSampleData('clean');

        $this->purifierDouble
            ->shouldReceive('purify')
            ->andReturnValues($cleanData);

        $this->signupForm->sanitize();
        $signupFormReflection = new \ReflectionClass($this->signupForm);
        $validateAgreeCocMethod = $signupFormReflection->getMethod('validateAgreeCoc');
        $validateAgreeCocMethod->setAccessible(true);
        $this->assertTrue($validateAgreeCocMethod->invoke($this->signupForm));
    }

    /**
     * Test that the form can correctly validate the agree coc field
     * when it is empty and when the corresponding option has not been set.
     *
     * @test
     */
    public function formCanCorrectlyValidateEmptyAgreeCocFieldWithoutOptionAndReturnsCorrespondingMessage()
    {
        $cleanData = $this->getSampleData('clean');
        $cleanData['agree_coc'] = '';

        $this->purifierDouble
            ->shouldReceive('purify')
            ->andReturnValues($cleanData);

        $this->signupForm = new SignupForm(
            $cleanData,
            $this->purifierDouble
        );

        $this->signupForm->sanitize();
        $signupFormReflection = new \ReflectionClass($this->signupForm);
        $validateAgreeCocMethod = $signupFormReflection->getMethod('validateAgreeCoc');
        $validateAgreeCocMethod->setAccessible(true);
        $this->assertFalse($validateAgreeCocMethod->invoke($this->signupForm));
        $this->assertSame(
            ['You must agree to abide by our code of conduct in order to submit'],
            $this->signupForm->getErrorMessages()
        );
    }

    /**
     * Test that the form can correctly validate the agree coc field
     * if the has_coc option has been set.
     *
     * @test
     */
    public function formCanCorrectlyValidateAgreeCocFieldWithOption()
    {
        $signupForm = new SignupForm(
            $this->taintedData,
            $this->purifierDouble,
            ['has_coc' => true]
        );

        $signupFormReflection = new \ReflectionClass($signupForm);
        $validateAgreeCocMethod = $signupFormReflection->getMethod('validateAgreeCoc');
        $validateAgreeCocMethod->setAccessible(true);
        $this->assertTrue($validateAgreeCocMethod->invoke($signupForm));
    }
}
