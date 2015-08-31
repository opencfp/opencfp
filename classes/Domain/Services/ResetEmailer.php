<?php

namespace OpenCFP\Domain\Services;

class ResetEmailer
{
    private $swift_mailer;
    private $template;
    private $config_email;
    private $config_title;

    public function __construct(\Swift_Mailer $swiftMailer, \Twig_Template $template, $configEmail, $configTitle)
    {
        $this->swift_mailer = $swiftMailer;
        $this->template = $template;
        $this->config_email = $configEmail;
        $this->config_title = $configTitle;
    }

    public function send($userId, $email, $resetCode)
    {
        $parameters = $this->parameters($userId, $resetCode);

        try {
            $message = $this->preparedMessage($email, $parameters);
            return $this->swift_mailer->send($message);
        } catch (\Exception $e) {
            echo $e;
            die();
        }
    }

    private function parameters($userId, $resetCode)
    {
        return array(
            'reset_code' => $resetCode,
            'method' => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off')
                ? 'https' : 'http',
            'host' => !empty($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'localhost',
            'user_id' => $userId,
            'email' => $this->config_email,
            'title' => $this->config_title
        );
    }

    private function preparedMessage($email, $parameters)
    {
        $message = new \Swift_Message();

        $message->setTo($email);
        $message->setFrom(
            $this->template->renderBlock('from', $parameters),
            $this->template->renderBlock('from_name', $parameters)
        );

        $message->setSubject($this->template->renderBlock('subject', $parameters));
        $message->setBody($this->template->renderBlock('body_text', $parameters));
        $message->addPart(
            $this->template->renderBlock('body_html', $parameters),
            'text/html'
        );

        return $message;
    }
}
