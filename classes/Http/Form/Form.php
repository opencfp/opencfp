<?php
namespace OpenCFP\Http\Form;

abstract class Form
{
    protected $_options;
    protected $_messages;
    protected $_cleanData;
    protected $_taintedData;
    protected $_purifier;
    protected $_fieldList = array();

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
        $this->_messages    = array();
        $this->_cleanData   = array();
        $this->_taintedData = array();

        $this->populate($data);
    }

    /**
     * Populates the form with default data, clears previously set sanitized data
     *
     * @param array $data
     */
    public function populate(array $data)
    {
        $this->_taintedData = $data;
        $this->_cleanData = null;
    }

    /**
     * Updates the form's data.
     *
     * @param array $data
     */
    public function update(array $data)
    {
        // The $_taintedData property might have been already set by
        // the populate() method.
        $this->_taintedData = array_merge($this->_taintedData, (array) $data);
    }

    /**
     * Method that validates that we have all required
     * fields in our submitted data
     *
     * @return boolean
     */
    public function hasRequiredFields()
    {
        $dataKeys = array_keys($this->_taintedData);
        $foundFields = array_intersect($this->_fieldList, $dataKeys);

        return ($foundFields == $this->_fieldList);
    }

    /**
     * Returns the clean data.
     *
     * @array array $keys The wanted data
     * @param  array $keys
     * @return array The cleaned data
     */
    public function getCleanData(array $keys = array())
    {
        if (empty($keys)) {
            return $this->_cleanData;
        }

        $data = array();
        foreach ($keys as $key) {
            if (isset($this->_cleanData[$key])) {
                $data[$key] = $this->_cleanData[$key];
            }
        }

        return $data;
    }

    /**
     * Returns the value of a tainted data by its name.
     *
     * @param  string     $name    The tainted value name
     * @param  mixed      $default The default value to return if not set
     * @return mixed|null
     */
    public function getTaintedField($name, $default = null)
    {
        return isset($this->_taintedData[$name]) ? $this->_taintedData[$name] : $default;
    }

    /**
     * Returns the tainted data.
     *
     * @return array
     */
    public function getTaintedData()
    {
        return $this->_taintedData;
    }

    /**
     * Returns the value of a form's option if set.
     *
     * @param  string     $name    The option name
     * @param  mixed|null $default The default value
     * @return mixed      The option value
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
        return $this->_messages;
    }

    /**
     * Returns whether or not the form has error messages.
     *
     * @return boolean
     */
    public function hasErrors()
    {
        return count($this->_messages) > 0;
    }

    /**
     * Method that adds error message to our class attribute, making sure to
     * not add anything that is in there already
     *
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     *
     * @param string $message The error messages to add to the list
     */
    protected function _addErrorMessage($message)
    {
        if (!in_array($message, $this->_messages)) {
            $this->_messages[] = $message;
        }
    }

    /**
     * Method that sanitizes all data
     *
     */
    public function sanitize()
    {
        $this->_cleanData = $this->_sanitize($this->_taintedData);
    }

    /**
     * Sanitizes all fields that were submitted.
     *
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     *
     * @param  array $taintedData The tainted data
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
