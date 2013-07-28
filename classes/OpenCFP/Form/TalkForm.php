<?php
namespace OpenCFP\Form;

use OpenCFP\Model\Speaker;
/**
 * Class representing the form that speakers fill out when they want
 * to submit a talk
 */
class TalkForm extends Form
{
    protected $_field_list = array(
        'title',
        'description',
        'type',
        'user_id'
    );

    /**
     * Santize all our fields that were submitted
     *
     * @return array
     */
    public function sanitize()
    {
        parent::sanitize();

        foreach($this->_sanitized_data as $key => $value) {
            $this->_sanitized_data[$key] = strip_tags($value);
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
            $this->validateType()
        );
    }

    /**
     * Method that validates title data
     *
     * @return boolean
     */
    public function validateTitle()
    {
        if (empty($this->_data['title'])) {
            $this->_addErrorMessage("Please fill in the title");
            return false;
        }

        $title = $this->_sanitized_data['title'];

        if ($title !== $this->_data['title']) {
            $this->_addErrorMessage("You had invalid characters in your talk title");
            return false;
        }

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
        if (empty($this->_sanitized_data['description'])) {
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
        $validTalkTypes = array(
            'half-day-tutorial',
            'full-day-tutorial',
            'regular',
            'lightning'
        );

        if (empty($this->_sanitized_data['type']) || !isset($this->_sanitized_data['type'])) {
            $this->_addErrorMessage("You must choose what type of talk you are submitting");
            return false;
        }

        if (!in_array($this->_sanitized_data['type'], $validTalkTypes)) {
            $this->_addErrorMessage("You did not choose a valid talk type");
            return false;
        }

        return true;
    }

    /**
     * Method that validates we have a valid user_id
     *
     * @param Speaker $speaker
     * @return boolean
     */
    public function validateSpeakerId(Speaker $speaker)
    {
        $userId = $this->_sanitized_data['user_id'];
        $thisSpeaker = $speaker->findByUserId($userId);

        if (!$thisSpeaker) {
            $this->_addErrorMessage("Your talk does not seem to belong to a valid speaker");
            return false;
        }

        return true;
    }
}
