<?php

namespace Scheb\TwoFactorBundle\Tests\Security\TwoFactor\Session;

use Scheb\TwoFactorBundle\Security\TwoFactor\Session\SessionFlagGenerator;
use Scheb\TwoFactorBundle\Tests\TestCase;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

class SessionFlagGeneratorTest extends TestCase
{
    /**
     * @var SessionFlagGenerator
     */
    private $sessionFlagGenerator;

    protected function setUp()
    {
        $this->sessionFlagGenerator = new SessionFlagGenerator();
    }

    /**
     * @test
     */
    public function getSessionFlag_noProviderKey_returnSessionFlag()
    {
        $token = $this->createMock(TokenInterface::class);
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
        $token = $this->createMock(UsernamePasswordToken::class);
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
