<?php

namespace Scheb\TwoFactorBundle\Security\TwoFactor\Provider;

use Scheb\TwoFactorBundle\Security\TwoFactor\AuthenticationContextInterface;
use Symfony\Component\HttpFoundation\Response;

interface TwoFactorProviderInterface
{
    /**
     * Return true when two-factor authentication process should be started.
     *
     * @param AuthenticationContextInterface $context
     *
     * @return bool
     */
    public function beginAuthentication(AuthenticationContextInterface $context);

    /**
     * Ask for two-factor authentication code.
     * Providers can create a response or ignore the request by returning null.
     *
     * @param AuthenticationContextInterface $context
     *
     * @return Response|null
     */
    public function requestAuthenticationCode(AuthenticationContextInterface $context);
}
