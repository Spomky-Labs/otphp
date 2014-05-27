<?php
namespace Scheb\TwoFactorBundle\Security\TwoFactor\Session;

class SessionFlagGenerator
{

    /**
     * Generate session token
     *
     * @param  string Two-factor provider name
     * @param  \Symfony\Component\Security\Core\Authentication\Token\TokenInterface $token
     * @return string
     */
    public function getSessionFlag($provider, $token)
    {
        // Support provider key
        $providerKey = "any";
        if (method_exists($token, "getProviderKey")) {
            $providerKey = $token->getProviderKey();
        }

        return sprintf('two_factor_%s_%s_%s', $provider, $providerKey, $token->getUsername());
    }

}
