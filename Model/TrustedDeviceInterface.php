<?php

namespace Scheb\TwoFactorBundle\Model;

interface TrustedDeviceInterface
{
    /**
     * Return version for the trusted token. Increase version to invalidate all trusted token of the user.
     *
     * @return int
     */
    public function getTrustedTokenVersion(): int;
}
