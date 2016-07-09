<?php

namespace Scheb\TwoFactorBundle\Tests\Security\TwoFactor\Session;

use Scheb\TwoFactorBundle\Security\TwoFactor\Session\SessionFlagGenerator;

class SessionFlagGeneratorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var SessionFlagGenerator
     */
    private $sessionFlagGenerator;

    public function setUp()
    {
        $this->sessionFlagGenerator = new SessionFlagGenerator();
    }

    /**
     * @test
     */
    public function getSessionFlag_noProviderKey_returnSessionFlag()
    {
        $token = $this->createMock('Symfony\Component\Security\Core\Authentication\Token\TokenInterface');
        $token
            ->expects($this->once())
            ->method('getUsername')
            ->will($this->returnValue('username'));

        $returnValue = $this->sessionFlagGenerator->getSessionFlag('twoFactorProvider', $token);
        $this->assertEquals('two_factor_twoFactorProvider_any_username', $returnValue);
    }

    /**
     * @test
     */
    public function getSessionFlag_withProviderKey_returnSessionFlag()
    {
        $token = $this->getMockBuilder('Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken')
            ->disableOriginalConstructor()
            ->getMock();
        $token
            ->expects($this->once())
            ->method('getUsername')
            ->will($this->returnValue('username'));
        $token
            ->expects($this->once())
            ->method('getProviderKey')
            ->will($this->returnValue('providerKey'));

        $returnValue = $this->sessionFlagGenerator->getSessionFlag('twoFactorProvider', $token);
        $this->assertEquals('two_factor_twoFactorProvider_providerKey_username', $returnValue);
    }
}
