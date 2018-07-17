<?php

/*
 * The MIT License (MIT)
 *
 * Copyright (c) 2014-2018 Spomky-Labs
 *
 * This software may be modified and distributed under the terms
 * of the MIT license.  See the LICENSE file for details.
 */

namespace Scheb\TwoFactorBundle\Security\TwoFactor\Handler;

use Scheb\TwoFactorBundle\Security\TwoFactor\AuthenticationContextInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

interface AuthenticationHandlerInterface
{
    /**
     * Begin the two-factor authentication process.
     *
     * @param AuthenticationContextInterface $context
     *
     * @return TokenInterface
     */
    public function beginTwoFactorAuthentication(AuthenticationContextInterface $context): TokenInterface;
}
