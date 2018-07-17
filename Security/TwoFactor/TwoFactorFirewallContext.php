<?php

/*
 * The MIT License (MIT)
 *
 * Copyright (c) 2014-2018 Spomky-Labs
 *
 * This software may be modified and distributed under the terms
 * of the MIT license.  See the LICENSE file for details.
 */

namespace Scheb\TwoFactorBundle\Security\TwoFactor;

class TwoFactorFirewallContext
{
    private $firewallConfigs = [];

    public function __construct(array $firewallConfigs)
    {
        $this->firewallConfigs = $firewallConfigs;
    }

    public function getFirewallConfig(string $firewallName): TwoFactorFirewallConfig
    {
        if (!isset($this->firewallConfigs[$firewallName])) {
            throw new \InvalidArgumentException('Firewall "'.$firewallName.'" has no two-factor config.');
        }

        return $this->firewallConfigs[$firewallName];
    }
}
