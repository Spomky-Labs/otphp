<?php

/*
 * The MIT License (MIT)
 *
 * Copyright (c) 2014-2018 Spomky-Labs
 *
 * This software may be modified and distributed under the terms
 * of the MIT license.  See the LICENSE file for details.
 */

namespace Scheb\TwoFactorBundle\Tests\Security\TwoFactor;

use Scheb\TwoFactorBundle\Security\TwoFactor\TwoFactorFirewallConfig;
use Scheb\TwoFactorBundle\Security\TwoFactor\TwoFactorFirewallContext;
use Scheb\TwoFactorBundle\Tests\TestCase;

class TwoFactorFirewallContextTest extends TestCase
{
    /**
     * @var TwoFactorFirewallContext
     */
    private $firewallContext;

    protected function setUp()
    {
        $firewallConfig = new TwoFactorFirewallConfig(['foo' => 'bar']);
        $this->firewallContext = new TwoFactorFirewallContext(['firewallName' => $firewallConfig]);
    }

    /**
     * @test
     */
    public function getFirewallConfig_isRegistered_returnFirewallConfig()
    {
        $returnValue = $this->firewallContext->getFirewallConfig('firewallName');
        $this->assertInstanceOf(TwoFactorFirewallConfig::class, $returnValue);
    }

    /**
     * @test
     */
    public function getFirewallConfig_unknownFirewall_throwInvalidArgumentException()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->firewallContext->getFirewallConfig('unknownFirewallName');
    }
}
