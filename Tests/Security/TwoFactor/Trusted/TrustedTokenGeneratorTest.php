<?php

namespace Scheb\TwoFactorBundle\Tests\Security\TwoFactor\Trusted;

use Scheb\TwoFactorBundle\Security\TwoFactor\Trusted\TrustedTokenGenerator;
use Scheb\TwoFactorBundle\Tests\TestCase;

class TrustedTokenGeneratorTest extends TestCase
{
    /**
     * @test
     */
    public function generateToken_useSecureRandom_validToken()
    {
        $generator = new TrustedTokenGenerator(); //Use SecureRandom
        $token = $generator->generateToken(20);
        $this->assertEquals(20, strlen($token));
    }
}
