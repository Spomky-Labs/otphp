<?php

namespace Scheb\TwoFactorBundle\Tests\Security\TwoFactor\Trusted;

use Scheb\TwoFactorBundle\Security\TwoFactor\Trusted\TrustedTokenGenerator;

class TrustedTokenGeneratorTest extends \PHPUnit_Framework_TestCase
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
