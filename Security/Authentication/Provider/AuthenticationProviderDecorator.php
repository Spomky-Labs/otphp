<?php
namespace Scheb\TwoFactorBundle\Security\Authentication\Provider;

use Scheb\TwoFactorBundle\Security\Authentication\Token\TwoFactorToken;
use Symfony\Component\Security\Core\Authentication\Provider\AuthenticationProviderInterface;
use Symfony\Component\Security\Core\Authentication\Token\AnonymousToken;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class AuthenticationProviderDecorator implements AuthenticationProviderInterface
{
    /**
     * @var AuthenticationProviderInterface
     */
    private $decoratedAuthenticationProvider;

    /**
     * @var string
     */
    private $providerKey;

    public function __construct(AuthenticationProviderInterface $decoratedAuthenticationProvider, string $providerKey) {
        $this->decoratedAuthenticationProvider = $decoratedAuthenticationProvider;
        $this->providerKey = $providerKey;
    }

    public function authenticate(TokenInterface $token)
    {
        $token = $this->decoratedAuthenticationProvider->authenticate($token);
        if ($token instanceof AnonymousToken || $token instanceof TwoFactorToken) {
            return $token;
        }

        return new TwoFactorToken($token, null, $this->providerKey);
    }

    public function supports(TokenInterface $token) {
        return$this->decoratedAuthenticationProvider->supports($token);
    }
}
