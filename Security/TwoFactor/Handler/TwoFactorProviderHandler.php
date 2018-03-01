<?php

namespace Scheb\TwoFactorBundle\Security\TwoFactor\Handler;

use Scheb\TwoFactorBundle\Model\PreferredProviderInterface;
use Scheb\TwoFactorBundle\Security\Authentication\Token\InvalidProviderException;
use Scheb\TwoFactorBundle\Security\Authentication\Token\TwoFactorToken;
use Scheb\TwoFactorBundle\Security\TwoFactor\AuthenticationContextInterface;
use Scheb\TwoFactorBundle\Security\TwoFactor\Provider\TwoFactorProviderInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class TwoFactorProviderHandler implements AuthenticationHandlerInterface
{
    /**
     * @var TwoFactorProviderInterface[]
     */
    private $providers;

    public function __construct(iterable $providers)
    {
        $this->providers = $providers;
    }

    public function beginTwoFactorAuthentication(AuthenticationContextInterface $context): TokenInterface
    {
        $activeTwoFactorProviders = [];

        // Iterate over two-factor providers and begin the two-factor authentication process.
        foreach ($this->providers as $providerName => $provider) {
            if ($provider->beginAuthentication($context)) {
                $activeTwoFactorProviders[] = $providerName;
            }
        }

        $authenticatedToken = $context->getToken();
        if ($activeTwoFactorProviders) {
            $twoFactorToken = new TwoFactorToken($authenticatedToken, null, $context->getFirewallName(), $activeTwoFactorProviders);
            $this->setPreferredProvider($twoFactorToken, $context->getUser()); // Prioritize the user's preferred provider
            return $twoFactorToken;
        } else {
            return $authenticatedToken;
        }
    }

    private function setPreferredProvider(TwoFactorToken $token, $user): void
    {
        if ($user instanceof PreferredProviderInterface) {
            if ($preferredProvider = $user->getPreferredTwoFactorProvider()) {
                try {
                    $token->preferTwoFactorProvider($preferredProvider);
                } catch (InvalidProviderException $e) {
                }
            }
        }
    }
}
