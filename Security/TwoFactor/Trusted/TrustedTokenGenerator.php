<?php

namespace Scheb\TwoFactorBundle\Security\TwoFactor\Trusted;

class TrustedTokenGenerator
{
    /**
     * Generate trusted computer token.
     *
     * @param int $length
     *
     * @return string
     */
    public function generateToken($length)
    {
        return substr(base64_encode(random_bytes($length)), 0, $length);
    }
}
