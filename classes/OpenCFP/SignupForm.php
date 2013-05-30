<?php
namespace OpenCFP;

/**
 * Form object for our signup page, handles validation of form data
 */
class SignupForm
{
    protected $_data;
    public $errorMessages = array();
    protected $_purifier;

    /**
     * Class constructor
     *
     * @param $data array of $_POST data
     */
    public function __construct($data)
    {
        $this->_data = $data;
        $config = \HTMLPurifier_Config::createDefault();
        $this->_purifier = new \HTMLPurifier($config);
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

    /**
     * Validate all methods by calling all our validation methods
     *
     * @return boolean
     */
    public function validateAll()
    {
        /**
         * Grab all out fields that we are expecting and make sure that
         * they match after they've been sanitized
         */
        $sanitizedData = $this->sanitize();
        $originalData = array(
            'email' => $this->_data['email'],
            'password' => $this->_data['password'],
            'password2' => $this->_data['password2'],
            'first_name' => $this->_data['first_name'],
            'last_name' => $this->_data['last_name']
        );

        if (!empty($this->_data['speaker_info'])) {
            $originalData['speaker_info'] = $this->_data['speaker_info'];
        }

        $differences = array_diff($originalData, $sanitizedData);

        if (count($differences) > 0) {
            return false;
        }

        $validEmail = $this->validateEmail();
        $validPasswords = $this->validatePasswords();
        $validFirstName = $this->validateFirstName();
        $validLastName = $this->validateLastName();
        $validSpeakerInfo = true;

        if (!empty($this->_data['speaker_info'])) {
            $validSpeakerInfo = $this->validateSpeakerInfo();
        }

        return (
            $validEmail &&
            $validPasswords &&
            $validFirstName &&
            $validLastName &&
            $validSpeakerInfo
        );

    }

    /**
     * Method that applies validation rules to email 
     *
     * @param string $email
     */
    public function validateEmail()
    {
        if (!isset($this->_data['email'])) {
            return false;
        }

        $response = filter_var($this->_data['email'], FILTER_VALIDATE_EMAIL);

        return ($response !== false);
    }

    /**
     * Method that applies validation rules to user-submitted passwords
     *
     * @return true|string
     */
    public function validatePasswords()
    {
        $passwd = filter_var($this->_data['password'], FILTER_SANITIZE_STRING);
        $passwd2 = filter_var($this->_data['password2'], FILTER_SANITIZE_STRING);
        $validationResponse = true;

        if ($passwd == '' || $passwd2 == '') {
            $validationResponse = false;
            $this->errorMessages[] = "Missing passwords";
        }

        if ($passwd !== $passwd2) {
            $validationResponse = false;
            $this->errorMessages[] = "The submitted passwords do not match";
        }

        if (strlen($passwd) < 5 && strlen($passwd2) < 5) {
            $validationResponse = false;
            $this->errorMessages[] = "The submitted password must be at least 5 characters";
        }

        return $validationResponse; 
    }

    /**
     * Method that applies vaidation rules to user-submitted first names
     * 
     * @return boolean
     */
    public function validateFirstName()
    {
        $firstName = filter_var(
            $this->_data['first_name'], 
            FILTER_SANITIZE_STRING, 
            array('flags' => FILTER_FLAG_STRIP_HIGH)
        );
        $validationResponse = true;

        if ($firstName == '') {
            $this->errorMessages[] = 'First name cannot be blank';
            $validationResponse = false;
        }

        if (strlen($firstName) > 255) {
            $this->errorMessages[] = 'First name cannot exceed 255 characters';
            $validationResponse = false;
        }

        if ($firstName !== $this->_data['first_name']) {
            $this->errorMessages[] = 'First name contains unwanted characters';
            $validationResponse = false;
        }

        return $validationResponse;
    }


    /**
     * Method that applies vaidation rules to user-submitted first names
     * 
     * @return boolean
     */
    public function validateLastName()
    {
        $lastName = filter_var(
            $this->_data['last_name'], 
            FILTER_SANITIZE_STRING, 
            array('flags' => FILTER_FLAG_STRIP_HIGH)
        );
        $validationResponse = true;

        $lastName = strip_tags($lastName);

        if ($lastName == '') {
            $this->errorMessage[] = "Last name was blank or contained unwanted characters";
            $validationResponse = false;
        }

        if (strlen($lastName) > 255) {
            $this->errorMessage[] = "Last name cannot be longer than 255 characters";
            $validationResponse = false;
        }

        if ($lastName !== $this->_data['last_name']) {
            $this->errorMessage[] = "Last name data did not match after sanitizing";
            $validationResponse = false;
        }

        return $validationResponse;
    }

    /**
     * Method that applies validation rules to user-submitted speaker info
     *
     * @return boolean
     */
    public function validateSpeakerInfo()
    {
        $speakerInfo = filter_var(
            $this->_data['speaker_info'],
            FILTER_SANITIZE_STRING,
            array('flags' => FILTER_FLAG_STRIP_HIGH)
        );
        $validationResponse = true;
        $speakerInfo = strip_tags($speakerInfo);
        $speakerInfo = $this->_purifier->purify($speakerInfo);

        if ($speakerInfo !== $this->_data['speaker_info']) {
            $this->errorMessages[] = "Your submitted speaker info contained unwanted characters";
            $validationResponse = false;
        }

        if (empty($speakerInfo)) {
            $this->errorMessages[] = "You submitted speaker info but it was empty";
            $validationResponse = false;
        }

        return $validationResponse;
    }

    /**
     * Santize all our fields that were submitted
     *
     * @return array
     */
    public function sanitize()
    {
        $sanitizedData = array();

        foreach ($this->_data as $key => $value) {
            $sanitizedData[$key] = $this->_purifier->purify($value); 
        }

        return $sanitizedData;
    }

    /**
     * Build activation email
     *
     * @param $activationCode string
     * @param $message Swift_Message
     */
    private function constructActivationMessage($activationCode, $message)
    {
        global $twig;

        $template = $twig->loadTemplate('activation_email.twig');
        $parameters = array(
            'name' => $this->_data['first_name'],
            'activationCode' => $activationCode,
            'method' => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off')
                ? 'https' : 'http',
            'host' => !empty($_SERVER['HTTP_HOST'])
                ? $_SERVER['HTTP_HOST'] : 'localhost',
        );

        $message->setTo(
            $this->_data['email'],
            $this->_data['first_name'] . ' ' . $this->_data['last_name']
        );
        $message->setFrom(
            $template->renderBlock('from', $parameters),
            $template->renderBlock('from_name', $parameters)
        );
        $message->setSubject($template->renderBlock('subject', $parameters));
        $message->setBody($template->renderBlock('body_text', $parameters));
        $message->addPart(
            $template->renderBlock('body_html', $parameters),
            'text/html'
        );
    }

    /**
     * Send out activation email.  Returns # of emails sent which should be 1.
     *
     * @param $user \Cartalyst\Sentry\Users\Eloquent\User
     * @param null $transport \Swift_SmtpTransport
     * @param null $mailer \Swift_Mailer
     * @param null $message \Swift_Message
     * @return int
     */
    public function sendActivationMessage(
        $user,
        $transport = null,
        $mailer = null,
        $message = null
    )
    {
        $configuration = new Configuration();
        if (!$transport) {
            $transport = new \Swift_SmtpTransport();
        }
        $transport->setPort($configuration->getSMTPPort());
        $transport->setHost($configuration->getSMTPHost());
        $SMTPUser = $configuration->getSMTPUser();
        if ($SMTPUser) {
            $transport->setUsername($SMTPUser)
                ->setPassword($configuration->getSMTPPassword());
        }
        if (!$mailer) {
            $mailer = new \Swift_Mailer($transport);
        }
        if (!$message) {
            $message = new \Swift_Message();
        }
        $this->constructActivationMessage(
            $user->getActivationCode(),
            $message
        );
        return $mailer->send($message);
    }
}
