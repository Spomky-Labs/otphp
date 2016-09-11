<?php
namespace Scheb\TwoFactorBundle\Security\Authentication\Provider;

use Scheb\TwoFactorBundle\Security\Authentication\Token\TwoFactorToken;
use Symfony\Component\Security\Core\Authentication\Provider\AuthenticationProviderInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class TwoFactorProvider implements AuthenticationProviderInterface
{
    public function authenticate(TokenInterface $token)
    {
        /** @var TwoFactorToken $token */
        if (!$this->supports($token)) {
            return null;
        }
        if (!$token->getCredentials()) {
            return $token;
        }

        return $token->getAuthenticatedToken();
    }

    public function supports(TokenInterface $token)
    {
        return $token instanceof TwoFactorToken;
    }
}
