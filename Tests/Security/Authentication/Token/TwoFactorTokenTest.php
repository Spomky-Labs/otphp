<?php

namespace Scheb\TwoFactorBundle\Tests\Security\Authentication\Token;

use Scheb\TwoFactorBundle\Security\Authentication\Token\TwoFactorToken;
use Scheb\TwoFactorBundle\Tests\TestCase;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class TwoFactorTokenTest extends TestCase
{
    /**
     * @var TwoFactorToken
     */
    private $twoFactorToken;

    protected function setUp()
    {
        $twoFactorProviders = [
            'provider1',
            'provider2',
        ];
        $this->twoFactorToken = new TwoFactorToken($this->createMock(TokenInterface::class), null, 'firewallName', $twoFactorProviders);
    }

    /**
     * @test
     */
    public function preferTwoFactorProvider_preferOtherProvider_becomesCurrentProvider()
    {
        $this->twoFactorToken->preferTwoFactorProvider('provider2');
        $this->assertEquals('provider2', $this->twoFactorToken->getCurrentTwoFactorProvider());
    }

    /**
     * @test
     */
    public function getCurrentTwoFactorProvider_defaultOrderGiven_returnFirstProvider()
    {
        $this->assertEquals('provider1', $this->twoFactorToken->getCurrentTwoFactorProvider());
    }

    /**
     * @test
     */
    public function setTwoFactorProviderComplete_completeProvider_continueWithNextProvider()
    {
        $this->twoFactorToken->setTwoFactorProviderComplete('provider1');
        $this->assertEquals('provider2', $this->twoFactorToken->getCurrentTwoFactorProvider());
    }

    /**
     * @test
     */
    public function allTwoFactorProvidersAuthenticated_notComplete_returnFalse()
    {
        $this->twoFactorToken->setTwoFactorProviderComplete('provider1');
        $this->assertFalse($this->twoFactorToken->allTwoFactorProvidersAuthenticated());
    }

    /**
     * @test
     */
    public function allTwoFactorProvidersAuthenticated_allComplete_returnTrue()
    {
        $this->twoFactorToken->setTwoFactorProviderComplete('provider1');
        $this->twoFactorToken->setTwoFactorProviderComplete('provider2');
        $this->assertTrue($this->twoFactorToken->allTwoFactorProvidersAuthenticated());
    }
}
