<?php
namespace OpenCFP;

/**
 * Form object for our signup page, handles validation of form data
 */
class SignupForm
{
	protected $_data;

    /**
     * Class constructor
     *
     * @param $data array of $_POST data
     */
    public function __construct($data)
    {
    	$this->_data = $data;
    }

    /**
     * Method verifies we have all required fields in our POST data
     *
     * @returns boolean
     */
    public function hasRequiredFields()
    {
        // If any of our fields are empty, reject stuff
	    $fieldList = array(
	        'email', 
	        'password', 
	        'password2', 
	        'first_name', 
	        'last_name',
	        'speaker_info'
	    );

	    foreach ($fieldList as $field) {
	        if (!isset($this->_data[$field])) {
	            $allFieldsFound = false;
	            break;
	        }
	    }

	    return $allFieldsFound;
	}
}
