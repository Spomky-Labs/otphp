<?php

namespace Scheb\TwoFactorBundle\Model;

interface TrustedComputerInterface
{
    /**
     * Add a trusted computer token.
     *
     * @param string    $token
     * @param \DateTime $validUntil
     */
    public function addTrustedComputer($token, \DateTime $validUntil);

    /**
     * Validate a trusted computer token.
     *
     * @param string $token
     *
     * @return bool
     */
    public function isTrustedComputer($token);
}
