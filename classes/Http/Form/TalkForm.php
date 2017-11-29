<?php

declare(strict_types=1);

/**
 * Copyright (c) 2013-2017 OpenCFP
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @see https://github.com/opencfp/opencfp
 */

namespace OpenCFP\Http\Form;

/**
 * Class representing the form that speakers fill out when they want
 * to submit a talk
 */
class TalkForm extends Form
{
    protected $_fieldList = [
        'title',
        'description',
        'type',
        'level',
        'category',
        'desired',
        'slides',
        'other',
        'sponsor',
        'user_id',
    ];

    public function __construct(array $data, \HTMLPurifier $purifier, array $options = [])
    {
        if (!\array_key_exists('desired', $data) || $data['desired'] === null) {
            $data['desired'] = 0;
        }

        if (!\array_key_exists('sponsor', $data) || $data['sponsor'] === null) {
            $data['sponsor'] = 0;
        }

        parent::__construct($data, $purifier, $options);
    }

    /**
     * Santize all our fields that were submitted
     *
     * @return array
     */
    public function sanitize()
    {
        parent::sanitize();

        foreach ($this->_cleanData as $key => $value) {
            $this->_cleanData[$key] = \strip_tags($value);
        }
    }

    /**
     * Validate everything
     *
     * @return bool
     */
    public function validateAll($action = 'create')
    {
        return
            $this->validateTitle()       &&
            $this->validateDescription() &&
            $this->validateLevel()       &&
            $this->validateCategory()    &&
            $this->validateDesired()     &&
            $this->validateSlides()      &&
            $this->validateOther()       &&
            $this->validateSponsor();
    }

    /**
     * Method that validates title data
     *
     * @return bool
     */
    public function validateTitle(): bool
    {
        if (empty($this->_taintedData['title'])) {
            $this->_addErrorMessage('Please fill in the title');

            return false;
        }

        $title = $this->_cleanData['title'];

        if (\strlen($title) > 100) {
            $this->_addErrorMessage('Your talk title has to be 100 characters or less');

            return false;
        }

        return true;
    }

    /**
     * Method that validates description data
     *
     * @return bool
     */
    public function validateDescription(): bool
    {
        if (empty($this->_cleanData['description'])) {
            $this->_addErrorMessage('Your description was missing');

            return false;
        }

        return true;
    }

    /**
     * Method that validates talk types
     *
     * @return bool
     */
    public function validateType(): bool
    {
        $validTalkTypes = $this->getOption('types');

        if (empty($this->_cleanData['type']) || !isset($this->_cleanData['type'])) {
            $this->_addErrorMessage('You must choose what type of talk you are submitting');

            return false;
        }

        if (!isset($validTalkTypes[$this->_cleanData['type']])) {
            $this->_addErrorMessage('You did not choose a valid talk type');

            return false;
        }

        return true;
    }

    public function validateLevel(): bool
    {
        $validLevels = $this->getOption('levels');

        if (empty($this->_cleanData['level']) || !isset($this->_cleanData['level'])) {
            $this->_addErrorMessage('You must choose what level of talk you are submitting');

            return false;
        }

        if (!isset($validLevels[$this->_cleanData['level']])) {
            $this->_addErrorMessage('You did not choose a valid talk level');

            return false;
        }

        return true;
    }

    public function validateCategory(): bool
    {
        $validCategories = $this->getOption('categories');

        if (empty($this->_cleanData['category']) || !isset($this->_cleanData['category'])) {
            $this->_addErrorMessage('You must choose what category of talk you are submitting');

            return false;
        }

        if (!isset($validCategories[$this->_cleanData['category']])) {
            $this->_addErrorMessage('You did not choose a valid talk category');

            return false;
        }

        return true;
    }

    public function validateDesired(): bool
    {
        return true;
    }

    public function validateSlides(): bool
    {
        return true;
    }

    public function validateOther(): bool
    {
        return true;
    }

    public function validateSponsor(): bool
    {
        return true;
    }
}
