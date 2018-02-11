<?php
namespace Scheb\TwoFactorBundle\Security\Authentication\Provider;

use Scheb\TwoFactorBundle\Security\Authentication\Token\TwoFactorToken;
use Scheb\TwoFactorBundle\Security\TwoFactor\Provider\TwoFactorProviderInterface;
use Scheb\TwoFactorBundle\Security\TwoFactor\Trusted\TrustedComputerManager;
use Symfony\Component\Security\Core\Authentication\Provider\AuthenticationProviderInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

class TwoFactorAuthenticationProvider implements AuthenticationProviderInterface
{
    /**
     * @var TwoFactorProviderInterface[]
     */
    private $providers;

    /**
     * @var string
     */
    private $firewallName;

    /**
     * @var TrustedComputerManager
     */
    private $trustedComputerManager;

    public function __construct(iterable $providers, string $firewallName, TrustedComputerManager $trustedComputerManager) {
        $this->providers = $providers;
        $this->firewallName = $firewallName;
        $this->trustedComputerManager = $trustedComputerManager;
    }

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

        if ($this->checkAuthenticationCode($token)) {
            $authenticatedToken = $token->getAuthenticatedToken();
            $this->trustedComputerManager->addTrustedComputer($authenticatedToken->getUser(), $this->firewallName);

            return $authenticatedToken;
        } else {
            throw new AuthenticationException('Invalid two-factor authentication code.');
        }
    }

    public function supports(TokenInterface $token)
    {
        return $token instanceof TwoFactorToken && $this->firewallName === $token->getProviderKey();
    }

    private function checkAuthenticationCode(TwoFactorToken $token)
    {
        return $token->getCredentials() === '1';
    }
}
