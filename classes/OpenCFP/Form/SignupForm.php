<?php
namespace OpenCFP\Form;

/**
 * Form object for our signup & profile pages, handles validation of form data
 */
class SignupForm extends Form
{
    protected $_fieldList = array(
        'email',
        'password',
        'password2',
        'first_name',
        'last_name',
        'company',
        'twitter',
        'speaker_info',
        'speaker_bio'
    );

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
        $valid_company = $this->validateCompany();
        $valid_twitter = $this->validateTwitter();
        $valid_speaker_info = true;
        $valid_speaker_bio = true;

        if (!empty($this->_taintedData['speaker_info'])) {
            $valid_speaker_info = $this->validateSpeakerInfo();
        }

        if (!empty($this->_taintedData['speaker_bio'])) {
            $valid_speaker_bio = $this->validateSpeakerBio();
        }

        return (
            $valid_email &&
            $valid_passwords &&
            $valid_first_name &&
            $valid_last_name &&
            $valid_company &&
            $valid_twitter &&
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
        if (!isset($this->_taintedData['email']) || $this->_taintedData['email'] == '') {
            $this->_addErrorMessage("Missing email");
            return false;
        }

        $response = filter_var($this->_taintedData['email'], FILTER_VALIDATE_EMAIL);

        if (!$response) {
            $this->_addErrorMessage("Invalid email address format");
            return false;
        }

        return true;
    }

    /**
     * Method that applies validation rules to user-submitted passwords
     *
     * @return true|string
     */
    public function validatePasswords()
    {
        $passwd = $this->_cleanData['password'];
        $passwd2 = $this->_cleanData['password2'];

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
            $this->_cleanData['first_name'],
            FILTER_SANITIZE_STRING,
            array('flags' => FILTER_FLAG_STRIP_HIGH | FILTER_FLAG_STRIP_LOW)
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

        if ($first_name !== $this->_taintedData['first_name']) {
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
        $last_name = $this->_cleanData['last_name'];

        if (empty($last_name)) {
            $this->_addErrorMessage("Last name was blank or contained unwanted characters");
            return false;
        }

        if (strlen($last_name) > 255) {
            $this->_addErrorMessage("Last name cannot be longer than 255 characters");
            return false;
        }

        if ($last_name !== $this->_taintedData['last_name']) {
            $this->_addErrorMessage("Last name data did not match after sanitizing");
            return false;
        }

        return true;
    }
    
    public function validateCompany()
    {
        // $company = $this->_cleanData['company'];
        
        return true;
    }
    
    public function validateTwitter()
    {
        // $twitter = $this->_cleanData['twitter'];
        
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
            $this->_cleanData['speaker_info'],
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
            $this->_cleanData['speaker_bio'],
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
        parent::sanitize();

        // We shouldn't be sanitizing passwords, so reset them
        if (isset($this->_taintedData['password'])) {
            $this->_cleanData['password'] = $this->_taintedData['password'];
        }

        if (isset($this->_taintedData['password2'])) {
            $this->_cleanData['password2'] = $this->_taintedData['password2'];
        }
    }

//    /**
//     * Build activation email
//     *
//     * @param $activationCode string
//     * @param $message Swift_Message
//     * @param $twig Twig objecg
//     */
//    private function constructActivationMessage($activationCode, \Swift_Message $message, \Twig_Environment $twig)
//    {
//        $template = $twig->loadTemplate('activation_email.twig');
//        $parameters = array(
//            'name' => $this->_data['first_name'],
//            'activationCode' => $activationCode,
//            'method' => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off')
//                ? 'https' : 'http',
//            'host' => !empty($_SERVER['HTTP_HOST'])
//                ? $_SERVER['HTTP_HOST'] : 'localhost',
//        );
//
//        $message->setTo(
//            $this->_data['email'],
//            $this->_data['first_name'] . ' ' . $this->_data['last_name']
//        );
//        $message->setFrom(
//            $template->renderBlock('from', $parameters),
//            $template->renderBlock('from_name', $parameters)
//        );
//        $message->setSubject($template->renderBlock('subject', $parameters));
//        $message->setBody($template->renderBlock('body_text', $parameters));
//        $message->addPart(
//            $template->renderBlock('body_html', $parameters),
//            'text/html'
//        );
//    }
//
//    /**
//     * Send out activation email.  Returns # of emails sent which should be 1.
//     *
//     * @param $user \Cartalyst\Sentry\Users\Eloquent\User
//     * @param $smtp array
//     * @param $twig \Twig_Environment
//     * @param null $transport \Swift_SmtpTransport
//     * @param null $mailer \Swift_Mailer
//     * @param null $message \Swift_Message
//     * @return int
//     */
//    public function sendActivationMessage(
//        \Cartalyst\Sentry\Users\Eloquent\User $user,
//        $smtp,
//        \Twig_Environment $twig,
//        \Swift_SmtpTransport $transport = null,
//        \Swift_Mailer $mailer = null,
//        \Swift_Message $message = null
//    )
//    {
//        if (!$transport) {
//            $transport = new \Swift_SmtpTransport($smtp['smtp.host'], $smtp['smtp.port']);
//        }
//
//        if (!empty($smtp['smtp.user'])) {
//            $transport->setUsername($smtp['smtp.user'])
//                      ->setPassword($smtp['smtp.password']);
//        }
//
//        if (!empty($smtp['smtp.encryption'])) {
//            $transport->setEncryption($smtp['smtp.encryption']);
//        }
//        if (!$mailer) {
//            $mailer = new \Swift_Mailer($transport);
//        }
//        if (!$message) {
//            $message = new \Swift_Message();
//        }
//        $this->constructActivationMessage(
//            $user->getActivationCode(),
//            $message,
//            $twig
//        );
//        return $mailer->send($message);
//    }

}
