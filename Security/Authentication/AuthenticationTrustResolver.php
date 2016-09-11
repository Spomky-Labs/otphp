<?php
namespace Scheb\TwoFactorBundle\Security\Authentication;

use Scheb\TwoFactorBundle\Security\Authentication\Token\TwoFactorToken;
use Symfony\Component\Security\Core\Authentication\AuthenticationTrustResolver as BaseAuthenticationTrustResolver;
use Symfony\Component\Security\Core\Authentication\AuthenticationTrustResolverInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

/**
 * Need to extend the original AuthenticationTrustResolver, because the
 * ACL security bundle depends on that class instead of using the interface.
 */
class AuthenticationTrustResolver extends BaseAuthenticationTrustResolver {

    /**
     * @var AuthenticationTrustResolverInterface
     */
    private $decoratedTrustResolver;

    /**
     * @param AuthenticationTrustResolverInterface $decoratedTrustResolver
     */
    public function __construct(AuthenticationTrustResolverInterface $decoratedTrustResolver)
    {
        $this->decoratedTrustResolver = $decoratedTrustResolver;
    }

    /**
     * {@inheritdoc}
     */
    public function isAnonymous(TokenInterface $token = null)
    {
        return $this->isTwoFactorToken($token) || $this->decoratedTrustResolver->isAnonymous($token);
    }

    /**
     * {@inheritdoc}
     */
    public function isRememberMe(TokenInterface $token = null)
    {
        return $this->decoratedTrustResolver->isRememberMe($token);
    }

    /**
     * {@inheritdoc}
     */
    public function isFullFledged(TokenInterface $token = null)
    {
        return !$this->isTwoFactorToken($token) && $this->decoratedTrustResolver->isFullFledged();
    }

    /**
     * @param TokenInterface|null $token
     *
     * @return bool
     */
    private function isTwoFactorToken(TokenInterface $token = null)
    {
        return $token instanceof TwoFactorToken;
    }
}
