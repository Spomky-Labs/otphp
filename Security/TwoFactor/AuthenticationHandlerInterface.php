<?php
namespace Scheb\TwoFactorBundle\Security\TwoFactor;

interface AuthenticationHandlerInterface
{

    /**
     * Begin the two-factor authentication process
     *
     * @param \Scheb\TwoFactorBundle\Security\TwoFactor\AuthenticationContext $context
     */
    public function beginAuthentication(AuthenticationContext $context);

    /**
     * Request and validate authentication code
     *
     * @param  \Scheb\TwoFactorBundle\Security\TwoFactor\AuthenticationContext $context
     * @return \Symfony\Component\HttpFoundation\Response|null
     */
    public function requestAuthenticationCode(AuthenticationContext $context);

}
