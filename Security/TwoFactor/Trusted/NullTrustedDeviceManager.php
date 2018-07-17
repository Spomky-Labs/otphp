<?php

/*
 * The MIT License (MIT)
 *
 * Copyright (c) 2014-2018 Spomky-Labs
 *
 * This software may be modified and distributed under the terms
 * of the MIT license.  See the LICENSE file for details.
 */

namespace Scheb\TwoFactorBundle\Security\TwoFactor\Trusted;

class NullTrustedDeviceManager implements TrustedDeviceManagerInterface
{
    public function addTrustedDevice($user, string $firewallName): void
    {
    }

    public function isTrustedDevice($user, string $firewallName): bool
    {
        return false;
    }
}
