<?php

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
