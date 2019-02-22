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

namespace OpenCFP\Http\Form;

use Symfony\Component\HttpFoundation;

/**
 * Form object for our signup & profile pages, handles validation of form data
 */
class SignupForm extends Form
{
    public const MUST_PROVIDE_AIRPORT_CODE_ERROR = "Since you specified that you'll need help with transportation costs, we need to know which airport you'll be departing from. You may specify additional information (e.g. alternate airports) in the Additional Notes field.";

    protected $fieldList = [
        'email',
        'password',
        'password2',
        'first_name',
        'last_name',
        'company',
        'twitter',
        'speaker_info',
        'speaker_bio',
        'transportation',
        'hotel',
        'speaker_photo',
        'agree_coc',
        'joindin_username',
        'url',
    ];

    public function validateAll(string $action = 'create'): bool
    {
        $this->sanitize();

        $validPasswords = true;
        $agreeCoc       = true;

        if ($action === 'create') {
            $validPasswords = $this->validatePasswords();
            $agreeCoc       = $this->validateAgreeCoc();
        }

        $validSpeakerInfo = true;

        if (!empty($this->taintedData['speaker_info'])) {
            $validSpeakerInfo = $this->validateSpeakerInfo();
        }

        $validSpeakerBio = true;

        if (!empty($this->taintedData['speaker_bio'])) {
            $validSpeakerBio = $this->validateSpeakerBio();
        }

        return $this->validateEmail() && $validPasswords && $this->validateFirstName() && $this->validateLastName() && $this->validateUrl() && $validSpeakerInfo && $validSpeakerBio && $this->validateSpeakerPhoto() && $agreeCoc && $this->validateJoindInUsername() && $this->validateTransportationRequests();
    }

    public function validateTransportationRequests(): bool
    {
        if (!isset($this->taintedData['transportation']) || !$this->taintedData['transportation']) {
            return true;
        }

        if (!isset($this->taintedData['airport']) || !$this->taintedData['airport']) {
            $this->addErrorMessage(static::MUST_PROVIDE_AIRPORT_CODE_ERROR);

            return false;
        }

        return true;
    }

    public function validateSpeakerPhoto(): bool
    {
        $allowedMimeTypes = [
            'image/jpeg',
            'image/jpg',
            'image/png',
        ];

        // Speaker Photo is not required, only validate if it exists
        if (!isset($this->taintedData['speaker_photo'])) {
            return true;
        }

        /** @var HttpFoundation\File\UploadedFile $speakerPhoto */
        $speakerPhoto = $this->taintedData['speaker_photo'];

        // Check if the file was uploaded OK, display any error that may have occurred
        if (!$speakerPhoto->isValid()) {
            $this->addErrorMessage($speakerPhoto->getErrorMessage());

            return false;
        }

        // Check if uploaded file is greater than 5MB
        if ($speakerPhoto->getClientSize() > (5 * 1048576)) {
            $this->addErrorMessage('Speaker photo can not be larger than 5MB');

            return false;
        }

        // Check if photo is in the mime-type white list
        if (!\in_array($speakerPhoto->getMimeType(), $allowedMimeTypes)) {
            $this->addErrorMessage('Speaker photo must be a jpg or png');

            return false;
        }

        return true;
    }

    public function validateEmail(): bool
    {
        if (!isset($this->taintedData['email']) || $this->taintedData['email'] == '') {
            $this->addErrorMessage('Missing email');

            return false;
        }

        $response = \filter_var($this->taintedData['email'], FILTER_VALIDATE_EMAIL);

        if (!$response) {
            $this->addErrorMessage('Invalid email address format');

            return false;
        }

        return true;
    }

    public function validatePasswords(): bool
    {
        $passwd  = $this->cleanData['password'];
        $passwd2 = $this->cleanData['password2'];

        if ($passwd == '' || $passwd2 == '') {
            $this->addErrorMessage('Missing passwords');

            return false;
        }

        if ($passwd !== $passwd2) {
            $this->addErrorMessage('The submitted passwords do not match');

            return false;
        }

        if (\strlen($passwd) < 5 && \strlen($passwd2) < 5) {
            $this->addErrorMessage('The submitted password must be at least 5 characters long');

            return false;
        }

        if ($passwd !== \str_replace(' ', '', $passwd)) {
            $this->addErrorMessage('The submitted password contains invalid characters');

            return false;
        }

        return true;
    }

    public function validateFirstName(): bool
    {
        $firstName          = $this->cleanData['first_name'];
        $validationResponse = true;

        if (empty($firstName)) {
            $this->addErrorMessage('First name cannot be blank');
            $validationResponse = false;
        }

        if (\strlen($firstName) > 255) {
            $this->addErrorMessage('First name cannot exceed 255 characters');
            $validationResponse = false;
        }

        if ($firstName !== $this->taintedData['first_name']) {
            $this->addErrorMessage('First name contains unwanted characters');
            $validationResponse = false;
        }

        return $validationResponse;
    }

    public function validateLastName(): bool
    {
        $lastName           = $this->cleanData['last_name'];
        $validationResponse = true;

        if (empty($lastName)) {
            $this->addErrorMessage('Last name cannot be blank');
            $validationResponse = false;
        }

        if (\strlen($lastName) > 255) {
            $this->addErrorMessage('Last name cannot exceed 255 characters');
            $validationResponse = false;
        }

        if ($lastName !== $this->taintedData['last_name']) {
            $this->addErrorMessage('Last name contains unwanted characters');
            $validationResponse = false;
        }

        return $validationResponse;
    }

    public function validateJoindInUsername(): bool
    {
        if (!isset($this->cleanData['joindin_username'])
            || $this->cleanData['joindin_username'] == ''
            || \preg_match('/^[a-zA-Z0-9\-_\.]{1,100}$/', $this->cleanData['joindin_username'])
        ) {
            return true;
        }

        $this->addErrorMessage('Please enter a valid joind.in username.');

        return false;
    }

    public function validateUrl(): bool
    {
        if (\filter_var($this->cleanData['url'], FILTER_VALIDATE_URL) !== false
            || !isset($this->cleanData['url'])
            || $this->cleanData['url'] == ''
        ) {
            return true;
        }
        $this->addErrorMessage('Please enter a valid URL.');

        return false;
    }

    public function validateSpeakerInfo(): bool
    {
        $speakerInfo = \filter_var(
            $this->cleanData['speaker_info'],
            FILTER_SANITIZE_STRING
        );
        $validationResponse = true;
        $speakerInfo        = \strip_tags($speakerInfo);
        $speakerInfo        = $this->purifier->purify($speakerInfo);

        if (empty($speakerInfo)) {
            $this->addErrorMessage('You submitted speaker info but it was empty after sanitizing');
            $validationResponse = false;
        }

        return $validationResponse;
    }

    public function validateSpeakerBio(): bool
    {
        $speakerBio = \filter_var(
            $this->cleanData['speaker_bio'],
            FILTER_SANITIZE_STRING
        );
        $validationResponse = true;
        $speakerBio         = \strip_tags($speakerBio);
        $speakerBio         = $this->purifier->purify($speakerBio);

        if (empty($speakerBio)) {
            $this->addErrorMessage('You submitted speaker bio information but it was empty after sanitizing');
            $validationResponse = false;
        }

        return $validationResponse;
    }

    public function sanitize()
    {
        parent::sanitize();

        // We shouldn't be sanitizing passwords, so reset them
        if (isset($this->taintedData['password'])) {
            $this->cleanData['password'] = $this->taintedData['password'];
        }

        if (isset($this->taintedData['password2'])) {
            $this->cleanData['password2'] = $this->taintedData['password2'];
        }

        // Remove leading @ for twitter
        if (isset($this->taintedData['twitter'])) {
            $this->cleanData['twitter'] = \preg_replace(
                '/^@/',
                '',
                $this->taintedData['twitter']
            );
        }
    }

    private function validateAgreeCoc(): bool
    {
        if (!$this->getOption('has_coc')) {
            return true;
        }

        if ($this->cleanData['agree_coc'] === 'agreed') {
            return true;
        }

        $this->addErrorMessage('You must agree to abide by our Code of Conduct in order to submit talks.');

        return false;
    }
}
