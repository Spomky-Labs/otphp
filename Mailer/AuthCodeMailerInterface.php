<?php

namespace Scheb\TwoFactorBundle\Mailer;

use Scheb\TwoFactorBundle\Model\Email\TwoFactorInterface;

interface AuthCodeMailerInterface
{
    /**
     * Send the auth code to the user via email.
     *
     * @param TwoFactorInterface $user
     */
    public function sendAuthCode(TwoFactorInterface $user);
}
