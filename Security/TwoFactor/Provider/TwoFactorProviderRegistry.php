<?php

/*
 * The MIT License (MIT)
 *
 * Copyright (c) 2014-2018 Spomky-Labs
 *
 * This software may be modified and distributed under the terms
 * of the MIT license.  See the LICENSE file for details.
 */

namespace Scheb\TwoFactorBundle\Security\TwoFactor\Provider;

use Scheb\TwoFactorBundle\Security\TwoFactor\Provider\Exception\UnknownTwoFactorProviderException;

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

        throw new UnknownTwoFactorProviderException('Two-factor provider "'.$providerName.'" does not exist.');
    }
}
