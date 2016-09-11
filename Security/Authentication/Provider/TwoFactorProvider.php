<?php
namespace Scheb\TwoFactorBundle\Security\Authentication\Provider;

use Scheb\TwoFactorBundle\Security\Authentication\Token\TwoFactorToken;
use Symfony\Component\Security\Core\Authentication\Provider\AuthenticationProviderInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

class TwoFactorProvider implements AuthenticationProviderInterface
{
    /**
     * @var string
     */
    private $providerKey;

    /**
     * @param string $providerKey
     */
    public function __construct($providerKey) {
        $this->providerKey = $providerKey;
    }

    /**
     * @inheritdoc
     */
    public function authenticate(TokenInterface $token)
    {
        /** @var TwoFactorToken $token */
        if (!$this->supports($token)) {
            return null;
        }

        // Keep unauthenticated TwoFactorToken with no credentials given
        if ($token->getCredentials() === null) {
            return $token;
        }

        if ($token->getCredentials() === '1') { // TODO: check authentication code
            return $token->getAuthenticatedToken();
        } else {
            throw new AuthenticationException('Invalid two-factor authentication code.');
        }
    }

    /**
     * @inheritdoc
     */
    public function supports(TokenInterface $token)
    {
        return $token instanceof TwoFactorToken && $this->providerKey === $token->getProviderKey();
    }
}
