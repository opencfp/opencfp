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

use Swift_Mailer;
use Swift_SmtpTransport;

class ResetEmailer
{
    /**
     * @var \Swift_Mailer
     */
    private $swiftMailer;

    /**
     * @var \Twig_Environment
     */
    private $twig;

    /**
     * @var string
     */
    private $configEmail;

    /**
     * @var string
     */
    private $configTitle;

    /**
     * @param \Twig_Environment $twig
     * @param string            $configEmail
     * @param string            $configTitle
     * @param array             $options
     */
    public function __construct(\Twig_Environment $twig, string $configEmail, string $configTitle, array $options)
    {
        $transport = (new Swift_SmtpTransport($options['host'], $options['port']))
            ->setUsername($options['username'])
            ->setPassword($options['password']);
        $this->swiftMailer = new Swift_Mailer($transport);

        $this->twig        = $twig;
        $this->configEmail = $configEmail;
        $this->configTitle = $configTitle;
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

            return $this->swiftMailer->send($message);
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
            'email'   => $this->configEmail,
            'title'   => $this->configTitle,
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

        /** @var \Twig_Template $template */
        $template = $this->twig->loadTemplate('emails/reset_password.twig');

        $message->setTo($email);
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

        return $message;
    }
}
