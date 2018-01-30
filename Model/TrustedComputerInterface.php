<?php

namespace Scheb\TwoFactorBundle\Model;

interface TrustedComputerInterface
{
    /**
     * Add a trusted computer trustedToken.
     *
     * @param string    $trustedToken
     * @param \DateTime $validUntil
     */
    public function addTrustedComputer(string $trustedToken, \DateTime $validUntil): void;

    /**
     * Validate a trusted computer trustedToken.
     *
     * @param string $trustedToken
     *
     * @return bool
     */
    public function isTrustedComputer(string $trustedToken): bool;
}
