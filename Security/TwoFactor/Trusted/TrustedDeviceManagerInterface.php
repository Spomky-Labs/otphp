<?php

namespace Scheb\TwoFactorBundle\Security\TwoFactor\Trusted;

interface TrustedDeviceManagerInterface
{
    /**
     * Add a trusted device token for a user.
     *
     * @param mixed  $user
     * @param string $firewallName
     */
    public function addTrustedDevice($user, string $firewallName): void;

    /**
     * Validate a device device token for a user.
     *
     * @param mixed  $user
     * @param string $firewallName
     *
     * @return bool
     */
    public function isTrustedDevice($user, string $firewallName): bool;
}
