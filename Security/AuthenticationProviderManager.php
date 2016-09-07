<?php
namespace Scheb\TwoFactorBundle\Security;

use Symfony\Component\Security\Core\Authentication\AuthenticationProviderManager as BaseAuthenticationProviderManager;
use Symfony\Component\Security\Core\Authentication\Token\AnonymousToken;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class AuthenticationProviderManager extends BaseAuthenticationProviderManager
{
    /**
     * {@inheritdoc}
     */
    public function authenticate(TokenInterface $token)
    {
        $token = parent::authenticate($token);
        if ($token instanceof AnonymousToken || $token instanceof TwoFactorToken) {
            return $token;
        }

        return new TwoFactorToken($token, '');
    }
}
