<?php

namespace OpenCFP\Form;

class TalkForm extends Form
{
    private static $expectedFields = array('id', 'title', 'description', 'type');

    /**
     * Validates the form's submitted data.
     *
     * @return array An array of cleaned values
     */
    protected function _validate()
    {
        // Sanitize the submitted data
        $sanitized = $this->_sanitize($this->getTaintedData());
        $differences = array_diff(self::$expectedFields, array_keys($sanitized));

        if (empty($sanitized) || count($differences) > 0) {
            $this->_addErrorMessage('You must have a title, description and select a talk type');
            return $sanitized;
        }

        // Sets the mandatory safe values
        $data['id'] = isset($sanitized['id']) ? (int) $sanitized['id'] : null;
        $data['user_id'] = isset($sanitized['user_id']) ? (int) $sanitized['user_id'] : null;

        // Apply all validator methods
        // Merge cleaned data arrays together
        $data = array_merge(
            $data,
            $this->_validateTitle($sanitized),
            $this->_validateDescription($sanitized),
            $this->_validateType($sanitized)
        );

        // Return the cleaned data
        return $data;
    }

    /**
     * Validates the title.
     *
     * @param array $taintedData The tainted data
     * @return array $cleaned The cleaned data
     */
    private function _validateTitle(array $taintedData)
    {
        $title = filter_var($taintedData['title'], FILTER_SANITIZE_STRING, array(
            'flags' => FILTER_FLAG_STRIP_HIGH,
        ));

        $errors = 0;
        if (empty($title)) {
            $errors++;
            $this->_addErrorMessage('You are missing a title');
        }

        if (strlen($title) > 100) {
            $errors++;
            $this->_addErrorMessage('Your talk title has to be 100 characters or less');
        }

        if ($title !== $taintedData['title']) {
            $errors++;
            $this->_addErrorMessage('You had invalid characters in your talk title');
        }

        $cleaned = array();
        if (!$errors) {
            $cleaned['title'] = $title;
        }

        return $cleaned;
    }

    /**
     * Validates the description.
     *
     * @param array $taintedData The tainted data
     * @return array $cleaned The cleaned data
     */
    private function _validateDescription(array $taintedData)
    {
        $description = filter_var($taintedData['description'], FILTER_SANITIZE_STRING, array(
            'flags' => FILTER_FLAG_STRIP_HIGH,
        ));

        $cleaned = array();
        if (empty($description) || $description !== $taintedData['description']) {
            $this->_addErrorMessage("Your description was missing or only contained invalid characters or content");
        } else {
            $cleaned['description'] = $description;
        }

        return $cleaned;
    }

    /**
     * Validates the type.
     *
     * @param array $taintedData The tainted data
     * @return array $cleaned The cleaned data
     */
    private function _validateType(array $taintedData)
    {
        $type = filter_var($taintedData['type'], FILTER_SANITIZE_STRING, array(
            'flags' => FILTER_FLAG_STRIP_HIGH,
        ));

        $type = strtolower($type);
        $errors = 0;
        if (empty($type)) {
            $errors++;
            $this->_addErrorMessage('You must choose what type of talk you are submitting');
        }

        if (!in_array($type, array_keys($this->getTalkTypes()))) {
            $errors++;
            $this->_addErrorMessage('You did not choose a valid talk type');
        }

        $cleaned = array();
        if (!$errors) {
            $cleaned['type'] = $type;
        }

        return $cleaned;
    }

    /**
     * Returns the list of talk types.
     *
     * @return array
     */
    public function getTalkTypes()
    {
        return array(
            'regular'           => 'Regular Talk',
            //'lightning'         => 'Lightning Talk',
            'half-day-tutorial' => 'Half-Day Tutorial',
            'full-day-tutorial' => 'Full-Day Tutorial',
        );
    }

    /**
     * Method that validates that we have all required
     * fields in our submitted data
     *
     * @todo to be removed?
     * @return boolean
     */
    public function hasRequiredFields()
    {
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
}