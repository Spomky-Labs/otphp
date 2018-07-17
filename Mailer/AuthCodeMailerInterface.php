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

interface AuthCodeMailerInterface
{
    /**
     * Send the auth code to the user via email.
     *
     * @param TwoFactorInterface $user
     */
    public function sendAuthCode(TwoFactorInterface $user): void;
}
