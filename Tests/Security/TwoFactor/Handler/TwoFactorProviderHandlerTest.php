<?php

namespace Scheb\TwoFactorBundle\Tests\Security\TwoFactor\Handler;

use PHPUnit\Framework\MockObject\MockObject;
use Scheb\TwoFactorBundle\Security\Authentication\Token\TwoFactorToken;
use Scheb\TwoFactorBundle\Security\TwoFactor\Handler\TwoFactorProviderHandler;
use Scheb\TwoFactorBundle\Security\TwoFactor\Provider\TwoFactorProviderInterface;

class TwoFactorProviderHandlerTest extends AuthenticationHandlerTestCase
{
    /**
     * @var MockObject|TwoFactorProviderInterface
     */
    private $provider1;

    /**
     * @var MockObject|TwoFactorProviderInterface
     */
    private $provider2;

    /**
     * @var \Scheb\TwoFactorBundle\Security\TwoFactor\Handler\TwoFactorProviderHandler
     */
    private $registry;

    protected function setUp()
    {
        $this->provider1 = $this->createMock(TwoFactorProviderInterface::class);
        $this->provider2 = $this->createMock(TwoFactorProviderInterface::class);
        $this->registry = new TwoFactorProviderHandler([
            'test1' => $this->provider1,
            'test2' => $this->provider2,
        ]);
    }

    private function stubProvidersReturn(bool $provider1Returns, bool $provider2Returns)
    {
        $this->provider1
            ->expects($this->any())
            ->method('beginAuthentication')
            ->willReturn($provider1Returns);

        $this->provider2
            ->expects($this->any())
            ->method('beginAuthentication')
            ->willReturn($provider2Returns);
    }

    /**
     * @test
     */
    public function beginAuthentication_multipleProviders_beginAuthenticationOnEachTwoFactorProvider()
    {
        $context = $this->createAuthenticationContext();

        $this->provider1
            ->expects($this->once())
            ->method('beginAuthentication')
            ->with($context);

        $this->provider2
            ->expects($this->once())
            ->method('beginAuthentication')
            ->with($context);

        $this->registry->beginTwoFactorAuthentication($context);
    }

    /**
     * @test
     */
    public function beginAuthentication_oneProviderStarts_returnTwoFactorToken()
    {
        $originalToken = $this->createToken();
        $context = $this->createAuthenticationContext(null, $originalToken);
        $this->stubProvidersReturn(false, true);

        /** @var TwoFactorToken $returnValue */
        $returnValue = $this->registry->beginTwoFactorAuthentication($context);
        $this->assertInstanceOf(TwoFactorToken::class, $returnValue);
        $this->assertSame($originalToken, $returnValue->getAuthenticatedToken());
        $this->assertEquals('firewallName', $returnValue->getProviderKey());
        $this->assertEquals(['test2'], $returnValue->getActiveTwoFactorProviders());
    }

    /**
     * @test
     */
    public function beginAuthentication_noProviderStarts_returnOriginalToken()
    {
        $originalToken = $this->createToken();
        $context = $this->createAuthenticationContext(null, $originalToken);
        $this->stubProvidersReturn(false, false);

        $returnValue = $this->registry->beginTwoFactorAuthentication($context);
        $this->assertSame($originalToken, $returnValue);
    }
}
