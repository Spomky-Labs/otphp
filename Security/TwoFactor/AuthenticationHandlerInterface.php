<?php

namespace Scheb\TwoFactorBundle\Security\TwoFactor;

use Symfony\Component\HttpFoundation\Response;

interface AuthenticationHandlerInterface
{
    /**
     * Begin the two-factor authentication process.
     *
     * @param AuthenticationContext $context
     */
    public function beginAuthentication(AuthenticationContext $context);

    /**
     * Request and validate authentication code.
     *
     * @param AuthenticationContext $context
     *
     * @return Response|null
     */
    public function requestAuthenticationCode(AuthenticationContext $context);
}
