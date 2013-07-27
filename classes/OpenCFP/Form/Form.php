<?php
namespace OpenCFP\Form;

abstract class Form {
    protected $_data;
    protected $_purifier;
    protected $_sanitized_data;
    protected $_field_list = array();

    public $error_messages = array();

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

        $dataKeys = array_keys($this->_data);
        $foundFields = array_intersect($this->_field_list, $dataKeys);

        return ($foundFields == $this->_field_list);
    }

    abstract public function validateAll($action = 'create');

    /**
     * Method that sanitizes all data
     *
     */
    public function sanitize()
    {
        $purifier = $this->_purifier;
        $this->_sanitized_data = array_map(
            function ($field) use ($purifier) {
                return $purifier->purify($field);
            },
            $this->_data
        );
    }

    /**
     * Return the array containing sanitized data
     *
     * @return array
     */
    public function getSanitizedData()
    {
        return $this->_sanitized_data;
    }

    /**
     * Method that adds error message to our class attribute, making sure to
     * not add anything that is in there already
     */
    protected function _addErrorMessage($message)
    {
        if (!in_array($message, $this->error_messages)) {
            $this->error_messages[] = $message;
        }
    }

}

?>
