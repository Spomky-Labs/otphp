<?php

namespace Scheb\TwoFactorBundle\Security\TwoFactor\Session;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class SessionFlagGenerator
{
    /**
     * Generate session token.
     *
     * @param string         $provider Two-factor provider name
     * @param TokenInterface $token
     *
     * @return string
     */
    public function getSessionFlag($provider, TokenInterface $token)
    {
        // Support provider key
        $providerKey = 'any';
        if (method_exists($token, 'getProviderKey')) {
            $providerKey = $token->getProviderKey();
        }

        return sprintf('two_factor_%s_%s_%s', $provider, $providerKey, $token->getUsername());
    }
}
