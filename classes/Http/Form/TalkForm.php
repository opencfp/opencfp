<?php

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
        'user_id'
    ];

    /**
     * Santize all our fields that were submitted
     *
     * @return array
     */
    public function sanitize()
    {
        parent::sanitize();

        foreach ($this->_cleanData as $key => $value) {
            $this->_cleanData[$key] = strip_tags($value);
        }
    }

    /**
     * Validate everything
     *
     * @return boolean
     */
    public function validateAll($action = 'create')
    {
        return (
            $this->validateTitle() &&
            $this->validateDescription() &&
            $this->validateLevel() &&
            $this->validateCategory() &&
            $this->validateDesired() &&
            $this->validateSlides() &&
            $this->validateOther() &&
            $this->validateSponsor()
        );
    }

    /**
     * Method that validates title data
     *
     * @return boolean
     */
    public function validateTitle()
    {
        if (empty($this->_taintedData['title'])) {
            $this->_addErrorMessage("Please fill in the title");

            return false;
        }

        $title = $this->_cleanData['title'];

        if (strlen($title) > 100) {
            $this->_addErrorMessage("Your talk title has to be 100 characters or less");

            return false;
        }

        return true;
    }

    /**
     * Method that validates description data
     *
     * @return boolean
     */
    public function validateDescription()
    {
        if (empty($this->_cleanData['description'])) {
            $this->_addErrorMessage("Your description was missing");

            return false;
        }

        return true;
    }

    /**
     * Method that validates talk types
     *
     * @return boolean
     */
    public function validateType()
    {
        $validTalkTypes = [
            'regular',
            'tutorial'
        ];

        if (empty($this->_cleanData['type']) || !isset($this->_cleanData['type'])) {
            $this->_addErrorMessage("You must choose what type of talk you are submitting");

            return false;
        }

        if (!in_array($this->_cleanData['type'], $validTalkTypes)) {
            $this->_addErrorMessage("You did not choose a valid talk type");

            return false;
        }

        return true;
    }

    public function validateLevel()
    {
        $validLevels = [
            'entry',
            'mid',
            'advanced'
        ];

        if (empty($this->_cleanData['level']) || !isset($this->_cleanData['level'])) {
            $this->_addErrorMessage("You must choose what level of talk you are submitting");

            return false;
        }

        if (!in_array($this->_cleanData['level'], $validLevels)) {
            $this->_addErrorMessage("You did not choose a valid talk level");

            return false;
        }

        return true;
    }

    public function validateCategory()
    {
        $validCategories = [
            'development',
            'framework',
            'database',
            'testing',
            'security',
            'devops',
            'api',
            'javascript',
            'uiux',
            'other',
            'continuousdelivery',
            'ibmi'
        ];

        if (empty($this->_cleanData['category']) || !isset($this->_cleanData['category'])) {
            $this->_addErrorMessage("You must choose what category of talk you are submitting");

            return false;
        }

        if (!in_array($this->_cleanData['category'], $validCategories)) {
            $this->_addErrorMessage("You did not choose a valid talk category");

            return false;
        }

        return true;
    }

    public function validateDesired()
    {
        return true;
    }

    public function validateSlides()
    {
        return true;
    }

    public function validateOther()
    {
        return true;
    }

    public function validateSponsor()
    {
        return true;
    }
}
