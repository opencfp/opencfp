<?php
namespace OpenCFP\Form;

/**
 * Form object for our signup page, handles validation of form data
 */
class SignupForm
{
    protected $_data;
    protected $_purifier;
    protected $_sanitized_data;
    public $error_messages = array();

    /**
     * Class constructor
     *
     * @param $data array of $_POST data
     * @param
     */
    public function __construct($data, $purifier)
    {
        $this->_data = $data;
        $this->_purifier = $purifier;
    }

    /**
     * Method verifies we have all required fields in our POST data
     *
     * @returns boolean
     */
    public function hasRequiredFields()
    {
        // If any of our fields are empty, reject stuff
        $all_fields_found = true;
        $field_list = array(
            'email',
            'password',
            'password2',
            'first_name',
            'last_name',
            'speaker_info'
        );

        foreach ($field_list as $field) {
            if (!isset($this->_data[$field])) {
                $all_fields_found = false;
                break;
            }
        }

        return $all_fields_found;
    }

    /**
     * Validate all methods by calling all our validation methods
     *
     * @param string $action
     * @return boolean
     */
    public function validateAll($action = 'create')
    {
        $this->sanitize();
        $valid_email = true;
        $valid_passwords = true;

        if ($action == 'create') {
            $valid_passwords = $this->validatePasswords();
        }

        $valid_email = $this->validateEmail();
        $valid_first_name = $this->validateFirstName();
        $valid_last_name = $this->validateLastName();
        $valid_speaker_info = true;
        $valid_speaker_bio = true;

        if (!empty($this->_data['speaker_info'])) {
            $valid_speaker_info = $this->validateSpeakerInfo();
        }

        if (!empty($this->data['speaker_bio'])) {
            $valid_speaker_bio = $this->validateSpeakerBio();
        }

        return (
            $valid_email &&
            $valid_passwords &&
            $valid_first_name &&
            $valid_last_name &&
            $valid_speaker_info &&
            $valid_speaker_bio
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
        $passwd = $this->_sanitized_data['password'];
        $passwd2 = $this->_sanitized_data['password2'];

        if ($passwd == '' || $passwd2 == '') {
            $this->_addErrorMessage("Missing passwords");
            return false;
        }

        if ($passwd !== $passwd2) {
            $this->_addErrorMessage("The submitted passwords do not match");
            return false;
        }

        if (strlen($passwd) < 5 && strlen($passwd2) < 5) {
            $this->_addErrorMessage("The submitted password must be at least 5 characters long");
            return false;
        }
        
        if ($passwd !== str_replace(" ", "", $passwd)) {
            $this->_addErrorMessage("The submitted password contains invalid characters");
            return false;
        }

        return true;
    }

    /**
     * Method that applies vaidation rules to user-submitted first names
     *
     * @return boolean
     */
    public function validateFirstName()
    {
        $first_name = filter_var(
            $this->_sanitized_data['first_name'],
            FILTER_SANITIZE_STRING,
            array('flags' => FILTER_FLAG_STRIP_HIGH)
        );
        $validation_response = true;

        if ($first_name == '') {
            $this->_addErrorMessage('First name cannot be blank');
            $validation_response = false;
        }

        if (strlen($first_name) > 255) {
            $this->_addErrorMessage('First name cannot exceed 255 characters');
            $validation_response = false;
        }

        if ($first_name !== $this->_data['first_name']) {
            $this->_addErrorMessage('First name contains unwanted characters');
            $validation_response = false;
        }

        return $validation_response;
    }


    /**
     * Method that applies vaidation rules to user-submitted first names
     *
     * @return boolean
     */
    public function validateLastName()
    {
        $last_name = $this->_sanitized_data['last_name'];

        if (empty($last_name)) {
            $this->_addErrorMessage("Last name was blank or contained unwanted characters");
            return false;
        }

        if (strlen($last_name) > 255) {
            $this->_addErrorMessage("Last name cannot be longer than 255 characters");
            return false;
        }

        if ($last_name !== $this->_data['last_name']) {
            $this->_addErrorMessage("Last name data did not match after sanitizing");
            return false;
        }

        return true;
    }

    /**
     * Method that applies validation rules to user-submitted speaker info
     *
     * @return boolean
     */
    public function validateSpeakerInfo()
    {
        $speakerInfo = filter_var(
            $this->_sanitized_data['speaker_info'],
            FILTER_SANITIZE_STRING
        );
        $validation_response = true;
        $speakerInfo = strip_tags($speakerInfo);
        $speakerInfo = $this->_purifier->purify($speakerInfo);

        if (empty($speakerInfo)) {
            $this->_addErrorMessage("You submitted speaker info but it was empty after sanitizing");
            $validation_response = false;
        }

        return $validation_response;
    }

    /**
     * Method that applies validation rules to user-submitted speaker bio
     *
     * @return boolean
     */
    public function validateSpeakerBio()
    {
        $speaker_bio = filter_var(
            $this->_sanitized_data['speaker_bio'],
            FILTER_SANITIZE_STRING
        );
        $validation_response = true;
        $speaker_bio = strip_tags($speaker_bio);
        $speaker_bio = $this->_purifier->purify($speaker_bio);

        if (empty($speaker_bio)) {
            $this->_addErrorMessage("You submitted speaker bio information but it was empty after sanitizing");
            $validation_response = false;
        }

        return $validation_response;
    }

    /**
     * Santize all our fields that were submitted
     *
     * @return array
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

        // We shouldn't be sanitizing passwords, so reset them
        if (isset($this->_data['password'])) {
            $this->_sanitized_data['password'] = $this->_data['password'];
        }

        if (isset($this->_data['password2'])) {
            $this->_sanitized_data['password2'] = $this->_data['password2'];
        }
    }

    /**
     * Build activation email
     *
     * @param $activationCode string
     * @param $message Swift_Message
     * @param $twig Twig objecg
     */
    private function constructActivationMessage($activationCode, \Swift_Message $message, \Twig_Environment $twig)
    {
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
     * @param $smtp array
     * @param $twig \Twig_Environment
     * @param null $transport \Swift_SmtpTransport
     * @param null $mailer \Swift_Mailer
     * @param null $message \Swift_Message
     * @return int
     */
    public function sendActivationMessage(
        \Cartalyst\Sentry\Users\Eloquent\User $user,
        $smtp,
        \Twig_Environment $twig,
        \Swift_SmtpTransport $transport = null,
        \Swift_Mailer $mailer = null,
        \Swift_Message $message = null
    )
    {
        if (!$transport) {
            $transport = new \Swift_SmtpTransport($smtp['smtp.host'], $smtp['smtp.port']);
        }

        if (!empty($smtp['smtp.user'])) {
            $transport->setUsername($smtp['smtp.user'])
                      ->setPassword($smtp['smtp.password']);
        }

        if (!empty($smtp['smtp.encryption'])) {
            $transport->setEncryption($smtp['smtp.encryption']);
        }
        if (!$mailer) {
            $mailer = new \Swift_Mailer($transport);
        }
        if (!$message) {
            $message = new \Swift_Message();
        }
        $this->constructActivationMessage(
            $user->getActivationCode(),
            $message,
            $twig
        );
        return $mailer->send($message);
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

    /**
     * Return the array containing sanitized data
     *
     * @return array
     */
    public function getSanitizedData()
    {
        return $this->_sanitized_data;
    }
}
