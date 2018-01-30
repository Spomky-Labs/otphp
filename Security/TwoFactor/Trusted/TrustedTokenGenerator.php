<?php

namespace Scheb\TwoFactorBundle\Security\TwoFactor\Trusted;

class TrustedTokenGenerator
{
    public function generateToken(int $length): string
    {
        return substr(base64_encode(random_bytes($length)), 0, $length);
    }
}
