<?php

namespace Scheb\TwoFactorBundle\Security\TwoFactor\Provider;

class TwoFactorProviderRegistry
{
    /**
     * @var TwoFactorProviderInterface[]
     */
    private $providers;

    public function __construct(iterable $providers)
    {
        $this->providers = $providers;
    }

    /**
     * @return iterable|TwoFactorProviderInterface[]
     */
    public function getAllProviders(): iterable
    {
        return $this->providers;
    }

    public function getProvider(string $providerName): TwoFactorProviderInterface
    {
        foreach ($this->providers as $name => $provider) {
            if ($name === $providerName) {
                return $provider;
            }
        }

        throw new \InvalidArgumentException('Provider "'.$providerName.'" does not exist.');
    }
}
