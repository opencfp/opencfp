<?php

/**
 * Copyright (c) 2013-2017 OpenCFP
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @see https://github.com/opencfp/opencfp
 */

namespace OpenCFP\Http\Form;

abstract class Form
{
    protected $_options;
    protected $_messages;
    protected $_cleanData;
    protected $_taintedData;
    protected $_purifier;
    protected $_fieldList = [];

    /**
     * @param $data array of form data
     * @param \HTMLPurifier $purifier
     * @param $options
     */
    public function __construct($data, \HTMLPurifier $purifier, array $options = [])
    {
        $this->_purifier    = $purifier;
        $this->_options     = $options;
        $this->_messages    = [];
        $this->_cleanData   = [];
        $this->_taintedData = [];

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
        $this->_cleanData   = null;
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
        $this->_taintedData = \array_merge($this->_taintedData, (array) $data);
    }

    /**
     * Method that validates that we have all required
     * fields in our submitted data
     *
     * @return bool
     */
    public function hasRequiredFields(): bool
    {
        $dataKeys    = \array_keys($this->_taintedData);
        $foundFields = \array_intersect($this->_fieldList, $dataKeys);

        return $foundFields == $this->_fieldList;
    }

    /**
     * Returns the clean data.
     *
     * @array array $keys The wanted data
     *
     * @param array $keys
     *
     * @return array The cleaned data
     */
    public function getCleanData(array $keys = [])
    {
        if (empty($keys)) {
            return $this->_cleanData;
        }

        $data = [];
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
     * @param string $name    The tainted value name
     * @param mixed  $default The default value to return if not set
     *
     * @return null|mixed
     */
    public function getTaintedField($name, $default = null)
    {
        return $this->_taintedData[$name] ?? $default;
    }

    /**
     * Returns the tainted data.
     *
     * @return array
     */
    public function getTaintedData(): array
    {
        return $this->_taintedData;
    }

    /**
     * Returns the value of a form's option if set.
     *
     * @param string     $name    The option name
     * @param null|mixed $default The default value
     *
     * @return mixed The option value
     */
    public function getOption($name, $default = null)
    {
        return $this->_options[$name] ?? $default;
    }

    /**
     * Validates the form's submitted data.
     */
    abstract public function validateAll($action = 'create');

    /**
     * Returns the list of error messages.
     *
     * @return array
     */
    public function getErrorMessages(): array
    {
        return $this->_messages;
    }

    /**
     * Returns whether or not the form has error messages.
     *
     * @return bool
     */
    public function hasErrors(): bool
    {
        return \count($this->_messages) > 0;
    }

    /**
     * Method that adds error message to our class attribute, making sure to
     * not add anything that is in there already
     *
     * @param string $message The error messages to add to the list
     */
    protected function _addErrorMessage($message)
    {
        if (!\in_array($message, $this->_messages)) {
            $this->_messages[] = $message;
        }
    }

    /**
     * Method that sanitizes all data
     */
    public function sanitize()
    {
        $this->_cleanData = $this->internalSanitize($this->_taintedData);
    }

    /**
     * Sanitizes all fields that were submitted.
     *
     * @param array $taintedData The tainted data
     *
     * @return array Sanitized data
     */
    protected function internalSanitize(array $taintedData): array
    {
        $purifier  = $this->_purifier;
        $filtered  = \array_map(
            function ($field) use ($purifier) {
                return $purifier->purify($field);
            },
            $taintedData
        );

        return $filtered;
    }
}
