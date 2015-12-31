<?php

namespace Scheb\TwoFactorBundle\Security\TwoFactor\Provider;

use Scheb\TwoFactorBundle\Security\TwoFactor\AuthenticationContext;
use Symfony\Component\HttpFoundation\Response;

interface TwoFactorProviderInterface
{
    /**
     * Return true when two-factor authentication process should be started.
     *
     * @param AuthenticationContext $context
     *
     * @return bool
     */
    public function beginAuthentication(AuthenticationContext $context);

    /**
     * Ask for two-factor authentication code.
     * Providers can create a response or ignore the request by returning null.
     *
     * @param AuthenticationContext $context
     *
     * @return Response|null
     */
    public function requestAuthenticationCode(AuthenticationContext $context);
}
