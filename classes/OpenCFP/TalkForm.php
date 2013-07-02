<?php
/**
 * Class representing the form that speakers fill out when they want
 * to submit a talk
 */
namespace OpenCFP;

class TalkForm
{
    protected $_data;
    public $error_messages = array();
    protected $_purifier;
    protected $_sanitzedData = array();

    /**
     * Class constructor
     *
     * @param $data array of form data
     */
    public function __construct($data, $purifier)
    {
        $this->_data = $data;
        $this->_purifier = $purifier;
    }

    /**
     * Method that validates that we have all required
     * fields in our submitted data
     *
     * @return boolean
     */
    public function hasRequiredFields()
    {
        $allFieldsFound = true;
        $fieldList = array(
            'title',
            'description',
            'type',
            'user_id'
        );

        $dataKeys = array_keys($this->_data);
        $foundFields = array_intersect($fieldList, $dataKeys);

        return ($foundFields == $fieldList);
    }

    /**
     * Method that sanitizes all data
     *
     * @param boolean $redo
     * @return array
     */
    public function sanitize()
    {
        $purifier = $this->_purifier;
        $this->_sanitized_data = array_map(
            function ($field) use ($purifier) {
                return strip_tags($purifier->purify($field));
            },
            $this->_data
        );

        return $this->_sanitized_data;
    }

    /**
     * Validate everything
     *
     * @return boolean
     */
    public function validateAll()
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
        $sanitized_data = $this->sanitize();

        if (empty($sanitized_data['title']) || !isset($sanitized_data['title'])) {
            $this->error_messages[] = "Your title contained content that could be used for XSS";
            return false;
        }

        $title = $sanitized_data['title'];

        if ($title !== $this->_data['title']) {
            $this->error_messages[] = "You had invalid characters in your talk title";
            return false;
        }

        if (strlen($title) > 100) {
            $this->error_messages[] = "Your talk title has to be 100 characters or less";
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
        $santizedData = $this->sanitize();

        if (empty($santizedData['description']) || $santizedData['description'] !== $this->_data['description']) {
            $this->error_messages[] = "Your description was missing or only contained invalid characters or content";
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
        $sanitized_data = $this->sanitize();
        $validTalkTypes = array(
            'half-day-tutorial',
            'full-day-tutorial',
            'regular',
            'lightning'
        );

        if (empty($sanitized_data['type']) || !isset($sanitized_data['type'])) {
            $this->error_messages[] = "You must choose what type of talk you are submitting";
            return false;
        }

        if (!in_array($sanitized_data['type'], $validTalkTypes)) {
            $this->error_messages[] = "You did not choose a valid talk type";
            return false;
        }

        return true;
    }

    /**
     * Method that validates we have a valid user_id
     *
     * @param \OpenCFP\Speaker $speaker
     * @return boolean
     */
    public function validateSpeakerId(\OpenCFP\Speaker $speaker)
    {
        $sanitized_data = $this->sanitize();
        $userId = $sanitized_data['user_id'];
        $thisSpeaker = $speaker->findByUserId($userId);

        if (!$thisSpeaker) {
            $this->error_messages[] = "Your talk does not seem to belong to a valid speaker";
            return false;
        }

        return true;
    }
}
