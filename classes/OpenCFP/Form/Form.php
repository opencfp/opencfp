<?php
namespace OpenCFP\Form;

abstract class Form
{
    protected $_options;
    protected $_data;
    protected $_purifier;
    protected $_sanitized_data;
    protected $_field_list = array();

    public $error_messages = array();

    /**
     * Class constructor
     *
     * @param $data array of form data
     * @param \HTMLPurifier $purifier
     * @param $options
     */
    public function __construct($data, \HTMLPurifier $purifier, array $options = array())
    {
        $this->_purifier    = $purifier;
        $this->_options     = $options;

        $this->populate($data);
    }

    /**
     * Populates the form with default data, clears previously set sanitized data
     *
     * @param array $data
     */
    public function populate(array $data)
    {
        $this->_data = $data;
        $this->_sanitized_data = null;
    }

    /**
     * Updates the form's data.
     *
     * @param array $data
     */
    public function update(array $data)
    {
        // The $_data property might have been already set by
        // the populate() method.
        $this->_data = array_merge($this->_data, (array) $data);
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

        $dataKeys = array_keys($this->_data);
        $foundFields = array_intersect($this->_field_list, $dataKeys);

        return ($foundFields == $this->_field_list);
    }

    /**
     * Returns the clean data.
     *
     * @return array The cleaned data
     */
    public function getSanitizedData()
    {
        return $this->_sanitized_data;
    }

    /**
     * Returns the value of a tainted data by its name.
     *
     * @param string $name The tainted value name
     * @param mixed $default The default value to return if not set
     */
    public function getTaintedField($name, $default = null)
    {
        return isset($this->_data[$name]) ? $this->_data[$name] : $default;
    }

    /**
     * Returns the tainted data.
     *
     * @return array
     */
    public function getTaintedData()
    {
        return $this->_data;
    }

    /**
     * Returns the value of a form's option if set.
     *
     * @param string $name The option name
     * @param mixed|null $default The default value
     * @return mixed The option value
     */
    public function getOption($name, $default = null)
    {
        return isset($this->_options[$name]) ? $this->_options[$name] : $default;
    }

    /**
     * Validates the form's submitted data.
     *
     */
    abstract public function validateAll($action = 'create');

    /**
     * Returns the list of error messages.
     *
     * @return array
     */
    public function getErrorMessages()
    {
        return $this->_error_messages;
    }

    /**
     * Returns whether or not the form has error messages.
     *
     * @return boolean
     */
    public function hasErrors()
    {
        return count($this->_error_messages) > 0;
    }

    /**
     * Method that adds error message to our class attribute, making sure to
     * not add anything that is in there already
     *
     * @param string $message The error messages to add to the list
     */
    protected function _addErrorMessage($message)
    {
        if (!in_array($message, $this->error_messages)) {
            $this->error_messages[] = $message;
        }
    }

    /**
     * Method that sanitizes all data
     *
     */
    public function sanitize()
    {
        $this->_sanitized_data = $this->_sanitize($this->_data);
    }

    /**
     * Sanitizes all fields that were submitted.
     *
     * @param array $taintedData The tainted data
     * @return array Sanitized data
     */
    protected function _sanitize(array $taintedData)
    {
        $purifier  = $this->_purifier;
        $filtered = array_map(
            function ($field) use ($purifier) {
                return $purifier->purify($field);
            },
            $taintedData
        );

        return $filtered;
    }
}
