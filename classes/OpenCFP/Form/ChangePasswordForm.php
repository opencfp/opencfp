<?php

namespace OpenCFP\Form;

class ChangePasswordForm extends SignupForm
{
    /**
     * Returns the cleaned password.
     *
     * @return string
     */
    public function getPassword()
    {
        return current($this->getData(array('password')));
    }

    /**
     * Validates the form's submitted data.
     *
     * @return array An array of cleaned values
     */
    protected function _validate()
    {
        // Sanitize the submitted data
        $sanitized = $this->_sanitize($this->getTaintedData());

        if (empty($sanitized)) {
            return $sanitized;
        }

        // Apply all validator methods
        // Merge cleaned data arrays together
        return $this->_validatePasswords($sanitized);
    }
}