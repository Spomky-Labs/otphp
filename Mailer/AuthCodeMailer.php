<?php

/*
 * The MIT License (MIT)
 *
 * Copyright (c) 2014-2018 Spomky-Labs
 *
 * This software may be modified and distributed under the terms
 * of the MIT license.  See the LICENSE file for details.
 */

namespace Scheb\TwoFactorBundle\Mailer;

use Scheb\TwoFactorBundle\Model\Email\TwoFactorInterface;

class AuthCodeMailer implements AuthCodeMailerInterface
{
    /**
     * @var \Swift_Mailer
     */
    private $mailer;

    /**
     * @var string
     */
    private $senderEmail;

    /**
     * @var string|null
     */
    private $senderName;

    public function __construct(\Swift_Mailer $mailer, string $senderEmail, ?string $senderName)
    {
        $this->mailer = $mailer;
        $this->senderEmail = $senderEmail;
        $this->senderName = $senderName;
    }

    public function sendAuthCode(TwoFactorInterface $user): void
    {
        $message = new \Swift_Message();
        $message
            ->setTo($user->getEmailAuthRecipient())
            ->setFrom($this->senderEmail, $this->senderName)
            ->setSubject('Authentication Code')
            ->setBody($user->getEmailAuthCode());
        $this->mailer->send($message);
    }
}
