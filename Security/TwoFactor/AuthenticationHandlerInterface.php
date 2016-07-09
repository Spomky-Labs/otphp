<?php

namespace Scheb\TwoFactorBundle\Security\TwoFactor;

use Symfony\Component\HttpFoundation\Response;

interface AuthenticationHandlerInterface
{
    /**
     * Begin the two-factor authentication process.
     *
     * @param AuthenticationContextInterface $context
     */
    public function beginAuthentication(AuthenticationContextInterface $context);

    /**
     * Request and validate authentication code.
     *
     * @param AuthenticationContextInterface $context
     *
     * @return Response|null
     */
    public function requestAuthenticationCode(AuthenticationContextInterface $context);
}
