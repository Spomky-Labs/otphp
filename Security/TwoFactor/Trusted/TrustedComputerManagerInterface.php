<?php
namespace Scheb\TwoFactorBundle\Security\TwoFactor\Trusted;

interface TrustedComputerManagerInterface
{
    /**
     * Add a trusted computer trustedToken for a user.
     *
     * @param mixed     $user
     * @param string    $trustedToken
     * @param \DateTime $validUntil
     */
    public function addTrustedComputer($user, string $trustedToken, \DateTime $validUntil);

    /**
     * Validate a trusted computer token for a user.
     *
     * @param mixed  $user
     * @param string $token
     *
     * @return bool
     */
    public function isTrustedComputer($user, string $token): bool;
}
