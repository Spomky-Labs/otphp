<?php

namespace Scheb\TwoFactorBundle\Security\TwoFactor\Provider;

use Scheb\TwoFactorBundle\Security\TwoFactor\AuthenticationContextInterface;

interface TwoFactorProviderInterface
{
    /**
     * Return true when two-factor authentication process should be started.
     *
     * @param AuthenticationContextInterface $context
     *
     * @return bool
     */
    public function beginAuthentication(AuthenticationContextInterface $context): bool;

    /**
     * Validate the two-factor authentication code.
     *
     * @param AuthenticationContextInterface $context
     * @param string $authenticationCode
     *
     * @return bool
     */
    public function validateAuthenticationCode(AuthenticationContextInterface $context, string $authenticationCode): bool;
}
