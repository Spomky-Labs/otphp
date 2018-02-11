<?php
namespace Scheb\TwoFactorBundle\Security\TwoFactor\Trusted;

interface TrustedComputerManagerInterface
{
    /**
     * Add a trusted computer token for a user.
     *
     * @param mixed $user
     * @param string $firewallName
     */
    public function addTrustedComputer($user, string $firewallName): void;

    /**
     * Validate a trusted computer token for a user.
     *
     * @param mixed  $user
     * @param string $firewallName
     *
     * @return bool
     */
    public function isTrustedComputer($user, string $firewallName): bool;
}
