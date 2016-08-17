<?php

namespace Scheb\TwoFactorBundle\Tests\Security\TwoFactor\Session;

use Scheb\TwoFactorBundle\Security\TwoFactor\Session\SessionFlagGenerator;
use Scheb\TwoFactorBundle\Tests\TestCase;

class SessionFlagGeneratorTest extends TestCase
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
            ->willReturn('username');

        $returnValue = $this->sessionFlagGenerator->getSessionFlag('twoFactorProvider', $token);
        $this->assertEquals('two_factor_twoFactorProvider_any_username', $returnValue);
    }

    /**
     * @test
     */
    public function getSessionFlag_withProviderKey_returnSessionFlag()
    {
        $token = $this->createMock('Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken');
        $token
            ->expects($this->once())
            ->method('getUsername')
            ->willReturn('username');
        $token
            ->expects($this->once())
            ->method('getProviderKey')
            ->willReturn('providerKey');

        $returnValue = $this->sessionFlagGenerator->getSessionFlag('twoFactorProvider', $token);
        $this->assertEquals('two_factor_twoFactorProvider_providerKey_username', $returnValue);
    }
}
