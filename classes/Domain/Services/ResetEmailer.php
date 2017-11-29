<?php

declare(strict_types=1);

/**
 * Copyright (c) 2013-2017 OpenCFP
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @see https://github.com/opencfp/opencfp
 */

namespace OpenCFP\Domain\Services;

class ResetEmailer
{
    /**
     * @var \Swift_Mailer
     */
    private $swift_mailer;

    /**
     * @var \Twig_Template
     */
    private $template;

    /**
     * @var string
     */
    private $config_email;

    /**
     * @var string
     */
    private $config_title;

    /**
     * @param \Swift_Mailer  $swiftMailer
     * @param \Twig_Template $template
     * @param string         $configEmail
     * @param string         $configTitle
     */
    public function __construct(\Swift_Mailer $swiftMailer, \Twig_Template $template, $configEmail, $configTitle)
    {
        $this->swift_mailer = $swiftMailer;
        $this->template     = $template;
        $this->config_email = $configEmail;
        $this->config_title = $configTitle;
    }

    /**
     * @param string $userId
     * @param string $email
     * @param string $resetCode
     *
     * @return int
     */
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

    /**
     * @param string $userId
     * @param string $resetCode
     *
     * @return array
     */
    private function parameters($userId, $resetCode)
    {
        return [
            'reset_code' => $resetCode,
            'method'     => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off')
                ? 'https' : 'http',
            'host'    => !empty($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'localhost',
            'user_id' => $userId,
            'email'   => $this->config_email,
            'title'   => $this->config_title,
        ];
    }

    /**
     * @param string $email
     * @param array  $parameters
     *
     * @return \Swift_Message
     */
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
