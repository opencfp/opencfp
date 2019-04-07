<?php

declare(strict_types=1);

/**
 * Copyright (c) 2013-2019 OpenCFP
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @see https://github.com/opencfp/opencfp
 */

namespace OpenCFP\Domain\Services;

use OpenCFP\Infrastructure\Templating\Template;
use Twig\Environment;

class ResetEmailer
{
    /**
     * @var \Swift_Mailer
     */
    private $swiftMailer;

    /**
     * @var \Twig\Environment
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
     * @param \Swift_Mailer $swiftMailer
     * @param Environment   $twig
     * @param string        $configEmail
     * @param string        $configTitle
     */
    public function __construct(\Swift_Mailer $swiftMailer, Environment $twig, string $configEmail, string $configTitle)
    {
        $this->swiftMailer = $swiftMailer;
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

        /** @var Template $template */
        $template = $this->twig->loadTemplate('emails/reset_password.twig');

        $message->setTo($email);
        $message->setFrom(
            $template->renderBlockWithContext('from', $parameters),
            $template->renderBlockWithContext('from_name', $parameters)
        );

        $message->setSubject($template->renderBlockWithContext('subject', $parameters));
        $message->setBody($template->renderBlockWithContext('body_text', $parameters));
        $message->addPart(
            $template->renderBlockWithContext('body_html', $parameters),
            'text/html'
        );

        return $message;
    }
}
