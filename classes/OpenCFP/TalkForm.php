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
     * Method that validates title data
     *
     * @return boolean
     */
    public function validateTitle()
    {
        $sanitizedData = $this->sanitize();

        if (empty($sanitizedData['title']) || !isset($sanitizedData['title'])) {
            return false;
        }

        $title = $sanitizedData['title'];

        if ($title !== $this->_data['title']) {
            return false;
        }

        if (empty($title) || $title === null) {
            return false;
        }

        if (strlen($title) > 100) {
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
            return false;
        }

        $description = $santizedData['description'];

        if ($description !== $this->_data['description']) {
            return false;
        }

        if (empty($description) || $description === null) {
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
            return false;
        }

        if (!in_array($sanitizedData['type'], $validTalkTypes)) {
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
            return false;
        }

        return true;
    }
}
