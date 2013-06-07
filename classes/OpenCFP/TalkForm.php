<?php
/**
 * Class representing the form that speakers fill out when they want
 * to submit a talk
 */
namespace OpenCFP;

class TalkForm
{
    protected $_data;
    public $errorMessages = array();
    protected $_purifier;
    protected $_sanitzedData = array();

    /**
     * Class constructor
     *
     * @param $data array of form data
     */
    public function __construct($data)
    {
        $this->_data = $data;
        $config = \HTMLPurifier_Config::createDefault();
        $this->_purifier = new \HTMLPurifier($config);
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
        $this->_sanitizedData = array_map(
            function ($field) use ($purifier) {
                return strip_tags($purifier->purify($field));
            },
            $this->_data
        );

        return $this->_sanitizedData;
    }

    /**
     * Validate everything
     *
     * @return boolean
     */
    public function validateAll()
    {
        $sanitizedData = $this->sanitize();
        $originalData = array(
            'title' => $this->_data['title'],
            'description' => $this->_data['description'],
            'type' => $this->_data['type']
        );

        $differences = array_diff($originalData, $sanitizedData);

        if (count($differences) > 0) {
            $this->errorMessages[] = "You must have a title, description and select a talk type";
            return false;
        }

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
        $sanitizedData = $this->sanitize();

        if (empty($sanitizedData['title']) || !isset($sanitizedData['title'])) {
            $this->errorMessages[] = "You are missing a title";
            return false;
        }

        $title = $sanitizedData['title'];

        if ($title !== $this->_data['title']) {
            $this->errorMessages[] = "You had invalid characters in your talk title";
            return false;
        }

        if (strlen($title) > 100) {
            $this->errorMessages[] = "Your talk title has to be 100 characters or less"; 
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

        if (empty($santizedData['description']) || !isset($santizedData['description'])) {
            $this->errorMessages[] = "Your description was missing or only contained invalid characters or content";
            return false;
        }

        $description = $santizedData['description'];

        if ($description !== $this->_data['description']) {
            $this->errorMessages[] = "You are missing a description for your talk";
            return false;
        }

        if (empty($description) || $description === null) {
            $this->errorMessages[] = "You are missing a description for your talk";
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
        $sanitizedData = $this->sanitize();
        $validTalkTypes = array(
            'half-day tutorial',
            'full-day tutorial',
            'regular',
            'lightning'
        );

        if (empty($sanitizedData['type']) || !isset($sanitizedData['type'])) {
            $this->errorMessages[] = "You must choose what type of talk you are submitting";
            return false;
        }

        if (!in_array($sanitizedData['type'], $validTalkTypes)) {
            $this->errorMessages[] = "You did not choose a valid talk type";
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
        $sanitizedData = $this->sanitize();
        $userId = $sanitizedData['user_id'];
        $thisSpeaker = $speaker->findByUserId($userId);
        
        if (!$thisSpeaker) {
            $this->errorMessages[] = "Your talk does not seem to belong to a valid speaker";
            return false;
        }

        return true;
    }
}
