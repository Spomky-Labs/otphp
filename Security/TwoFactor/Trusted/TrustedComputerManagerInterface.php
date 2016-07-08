<?php
namespace Scheb\TwoFactorBundle\Security\TwoFactor\Trusted;

interface TrustedComputerManagerInterface
{
    /**
     * Add a trusted computer token for a user.
     *
     * @param mixed     $user
     * @param string    $token
     * @param \DateTime $validUntil
     */
    public function addTrustedComputer($user, $token, \DateTime $validUntil);

    /**
     * Validate a trusted computer token for a user.
     *
     * @param mixed  $user
     * @param string $token
     *
     * @return bool
     */
    public function isTrustedComputer($user, $token);
}
