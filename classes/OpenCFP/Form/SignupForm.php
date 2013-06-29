<?php

namespace OpenCFP\Form;

//use Cartalyst\Sentry\Users\Eloquent\User;

/**
 * Form object for our signup page, handles validation of form data
 *
 */
class SignupForm extends Form
{
    /**
     * Returns the list of speaker data.
     *
     * @return array
     */
    public function getSpeakerData()
    {
        return array(
            'user'    => $this->getData(array('email', 'password', 'first_name', 'last_name')),
            'speaker' => $this->getData(array('bio', 'info')),
        );
    }

    /**
     * Validates the form.
     *
     * Only parent method to implement/override.
     *
     * @return array $data An array of cleaned data
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
        $data = array();
        if ('create' === $this->getOption('action')) {
            $data = array_merge($data, $this->_validatePasswords($sanitized));
        }

        if (!empty($sanitized['info'])) {
            $data = array_merge($data, $this->_validateSpeakerInfo($sanitized));
        }

        if (!empty($sanitized['bio'])) {
            $data = array_merge($data, $this->_validateSpeakerBio($sanitized));
        }

        $data = array_merge(
            $data,
            $this->_validateEmail($sanitized),
            $this->_validateFirstName($sanitized),
            $this->_validateLastName($sanitized)
        );

        // Return the cleaned data
        return $data;
    }

    /**
     * Method that applies validation rules to email
     *
     * @param array $taintedData The tainted data
     * @return array $cleaned An array of cleaned data
     */
    private function _validateEmail(array $taintedData)
    {
        $cleaned = array();
        if (!isset($taintedData['email'])) {
            return $cleaned;
        }

        $email = filter_var($taintedData['email'], FILTER_VALIDATE_EMAIL);
        if (!empty($email)) {
            $cleaned['email'] = $email;
        } else {
            $this->_addErrorMessage('The submitted email address is not valid');
        }

        return $cleaned;
    }

    /**
     * Method that applies validation rules to user-submitted passwords
     *
     * @param array $taintedData The tainted data
     * @return array $cleaned An array of cleaned data
     */
    protected function _validatePasswords(array $taintedData)
    {
        $passwd  = filter_var($taintedData['password'], FILTER_SANITIZE_STRING);
        $passwd2 = filter_var($taintedData['confirmation'], FILTER_SANITIZE_STRING);

        $errors = 0;
        if (empty($passwd) || empty($passwd2)) {
            $errors++;
            $this->_addErrorMessage('Missing passwords');
        }

        if ($passwd !== $passwd2) {
            $errors++;
            $this->_addErrorMessage('The submitted passwords do not match');
        }

        if (strlen($passwd) < 5 && strlen($passwd2) < 5) {
            $errors++;
            $this->_addErrorMessage('The submitted password must be at least 5 characters long');
        }

        $cleaned = array();
        if (!$errors) {
            $cleaned['password'] = $passwd;
        }

        return $cleaned;
    }

    /**
     * Method that applies vaidation rules to user-submitted first names
     *
     * @param array $taintedData The tainted data
     * @return array $cleaned An array of cleaned data
     */
    private function _validateFirstName(array $taintedData)
    {
        $firstName = filter_var($taintedData['first_name'], FILTER_SANITIZE_STRING, array(
            'flags' => FILTER_FLAG_STRIP_HIGH,
        ));

        $errors = 0;
        if (empty($firstName)) {
            $errors++;
            $this->_addErrorMessage('First name cannot be blank');
        }

        if (strlen($firstName) > 255) {
            $errors++;
            $this->_addErrorMessage('First name cannot exceed 255 characters');
        }

        if ($firstName !== $taintedData['first_name']) {
            $errors++;
            $this->_addErrorMessage('First name contains unwanted characters');
        }

        $cleaned = array();
        if (!$errors) {
            $cleaned['first_name'] = $firstName;
        }

        return $cleaned;
    }


    /**
     * Method that applies vaidation rules to user-submitted first names
     *
     * @param array $taintedData The tainted data
     * @return array $cleaned An array of cleaned data
     */
    private function _validateLastName(array $taintedData)
    {
        $lastName = filter_var($taintedData['last_name'], FILTER_SANITIZE_STRING, array(
            'flags' => FILTER_FLAG_STRIP_HIGH,
        ));

        $lastName = strip_tags($lastName);

        $errors = 0;
        if (empty($lastName)) {
            $errors++;
            $this->_addErrorMessage('Last name was blank or contained unwanted characters');
        }

        if (strlen($lastName) > 255) {
            $errors++;
            $this->_addErrorMessage('Last name cannot be longer than 255 characters');
        }

        if ($lastName !== $taintedData['last_name']) {
            $errors++;
            $this->_addErrorMessage('Last name data did not match after sanitizing');
        }

        $cleaned = array();
        if (!$errors) {
            $cleaned['last_name'] = $lastName;
        }

        return $cleaned;
    }

    /**
     * Method that applies validation rules to user-submitted speaker info
     *
     * @param array $taintedData The tainted data
     * @return array $cleaned An array of cleaned data
     */
    private function _validateSpeakerInfo(array $taintedData)
    {
        $data = filter_var($taintedData['info'], FILTER_SANITIZE_STRING);
        $data = strip_tags($data);

        $cleaned = array();
        if (!empty($data)) {
            $cleaned['info'] = $data;
        } else {
            $this->_addErrorMessage('You submitted speaker info but it was empty after sanitizing');
        }

        return $cleaned;
    }

    /**
     * Method that applies validation rules to user-submitted speaker bio
     *
     * @param array $taintedData The tainted data
     * @return array $cleaned An array of cleaned data
     */
    private function _validateSpeakerBio(array $taintedData)
    {
        $data = filter_var($taintedData['bio'], FILTER_SANITIZE_STRING);
        $data = strip_tags($data);

        $cleaned = array();
        if (!empty($data)) {
            $cleaned['bio'] = $data;
        } else {
            $this->_addErrorMessage('You submitted speaker bio information but it was empty after sanitizing');
        }

        return $cleaned;
    }

    // @todo Code below should be moved in a separate notification service class

    /**
     * Verifies we have all required fields in our submitted data
     *
     * @todo to be removed?
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
            'info'
        );

        $taintedData = $this->getTaintedData();
        foreach ($field_list as $field) {
            if (!isset($taintedData[$field])) {
                $all_fields_found = false;
                break;
            }
        }

        return $all_fields_found;
    }

    /*
    private function constructActivationMessage($activationCode, \Swift_Message $message, \Twig_Environment $twig)
    {
        // Get cleaned data
        $data = $this->getData();

        $template = $twig->loadTemplate('activation_email.twig');
        $parameters = array(
            'name' => $data['first_name'],
            'activationCode' => $activationCode,
            'method' => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off')
                ? 'https' : 'http',
            'host' => !empty($_SERVER['HTTP_HOST'])
                ? $_SERVER['HTTP_HOST'] : 'localhost',
        );

        $message->setTo(
            $data['email'],
            $data['first_name'] . ' ' . $data['last_name']
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

    public function sendActivationMessage(
        User $user,
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
    */
}
