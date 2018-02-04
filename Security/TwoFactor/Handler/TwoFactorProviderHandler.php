<?php

namespace Scheb\TwoFactorBundle\Security\TwoFactor\Handler;

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
        $this->providers   = $providers;
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

        $token = $context->getToken();
        if ($activeTwoFactorProviders) {
            return new TwoFactorToken($token, null, $context->getProviderKey(), $activeTwoFactorProviders);
        } else {
            return $token;
        }
    }
}
