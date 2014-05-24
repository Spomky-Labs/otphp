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
        $token = TrustedTokenGenerator::generateToken(20);
        $this->assertEquals(20, strlen($token));
    }

    /**
     * @test
     */
    public function generateToken_useFallback_validToken()
    {
        TestableTrustedTokenGenerator::$useSecureRandom = false; //Use fallback
        $token = TestableTrustedTokenGenerator::generateToken(20);
        $this->assertEquals(20, strlen($token));
        $this->assertRegExp("/^A+$/", $token);
    }

}

/**
 * Makes the TrustedTokenGenerator more testable
 */
class TestableTrustedTokenGenerator extends TrustedTokenGenerator
{
    public static $useSecureRandom = true; //Override generator selection

    protected static $charspace = "A"; //Override charspace

    protected static function useSecureRandom()
    {
        return self::$useSecureRandom;
    }

}
