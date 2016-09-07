<?php
namespace Scheb\TwoFactorBundle\Security\Authentication;

use Scheb\TwoFactorBundle\Security\TwoFactorToken;
use Symfony\Component\Security\Core\Authentication\AuthenticationTrustResolver as BaseAuthenticationTrustResolver;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

/**
 * Need to extend the original AuthenticationTrustResolver, because the
 * ACL security bundle depends on that class instead of using the interface.
 */
class AuthenticationTrustResolver extends BaseAuthenticationTrustResolver
{
    /**
     * {@inheritdoc}
     */
    public function isAnonymous(TokenInterface $token = null)
    {
        return parent::isAnonymous($token) || $this->isTwoFactorToken($token);
    }

    /**
     * @param TokenInterface|null $token
     *
     * @return bool
     */
    private function isTwoFactorToken(TokenInterface $token = null) {
        return $token instanceof TwoFactorToken;
    }
}
