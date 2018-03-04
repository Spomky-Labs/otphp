<?php

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
