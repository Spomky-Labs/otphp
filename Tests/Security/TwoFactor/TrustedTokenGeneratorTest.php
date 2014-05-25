<?php
namespace Scheb\TwoFactorBundle\Tests\Security\TwoFactor;

use Scheb\TwoFactorBundle\Security\TwoFactor\TrustedTokenGenerator;

class TrustedTokenGeneratorTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @test
     */
    public function generateToken_useSecureRandom_validToken()
    {
        $generator = new TestableTrustedTokenGenerator();
        $generator->useSecureRandom = true; //Use SecureRandom
        $token = $generator->generateToken(20);
        $this->assertEquals(20, strlen($token));
    }

    /**
     * @test
     */
    public function generateToken_useFallback_validToken()
    {
        $generator = new TestableTrustedTokenGenerator();
        $generator->useSecureRandom = false; //Use fallback
        $token = $generator->generateToken(20);
        $this->assertEquals(20, strlen($token));
        $this->assertRegExp("/^A+$/", $token);
    }

}

/**
 * Makes the TrustedTokenGenerator more testable
 */
class TestableTrustedTokenGenerator extends TrustedTokenGenerator
{
    public $useSecureRandom = true; //Override generator selection

    protected $charspace = "A"; //Override charspace

    protected function useSecureRandom()
    {
        return $this->useSecureRandom;
    }

}
