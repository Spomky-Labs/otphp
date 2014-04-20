<?php
namespace Scheb\TwoFactorBundle\Security\TwoFactor\Provider;

use Scheb\TwoFactorBundle\Security\TwoFactor\AuthenticationContext;

interface TwoFactorProviderInterface
{

    /**
     * Return true when two factor authentication process should be started
     *
     * @param \Scheb\TwoFactorBundle\Security\TwoFactor\AuthenticationContext $context
     * @return boolean
     */
    public function beginAuthentication(AuthenticationContext $context);

    /**
     * Ask for two factor authentication code.
     * Providers can create a response or ignore the request by returning null.
     *
     * @param \Scheb\TwoFactorBundle\Security\TwoFactor\AuthenticationContext $context
     * @return \Symfony\Component\HttpFoundation\Response|null
     */
    public function requestAuthenticationCode(AuthenticationContext $context);
}