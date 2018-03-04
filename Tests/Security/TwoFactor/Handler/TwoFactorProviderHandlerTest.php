<?php

namespace Scheb\TwoFactorBundle\Tests\Security\TwoFactor\Handler;

use PHPUnit\Framework\MockObject\MockObject;
use Scheb\TwoFactorBundle\Model\PreferredProviderInterface;
use Scheb\TwoFactorBundle\Security\Authentication\Token\TwoFactorToken;
use Scheb\TwoFactorBundle\Security\TwoFactor\Handler\TwoFactorProviderHandler;
use Scheb\TwoFactorBundle\Security\TwoFactor\Provider\TwoFactorProviderInterface;
use Scheb\TwoFactorBundle\Security\TwoFactor\Provider\TwoFactorProviderRegistry;

class TwoFactorProviderHandlerTest extends AuthenticationHandlerTestCase
{
    /**
     * @var MockObject|TwoFactorProviderRegistry
     */
    private $providerRegistry;

    /**
     * @var MockObject|TwoFactorProviderInterface
     */
    private $provider1;

    /**
     * @var MockObject|TwoFactorProviderInterface
     */
    private $provider2;

    /**
     * @var TwoFactorProviderHandler
     */
    private $handler;

    protected function setUp()
    {
        $this->provider1 = $this->createMock(TwoFactorProviderInterface::class);
        $this->provider2 = $this->createMock(TwoFactorProviderInterface::class);

        $this->providerRegistry = $this->createMock(TwoFactorProviderRegistry::class);
        $this->providerRegistry
            ->expects($this->any())
            ->method('getAllProviders')
            ->willReturn([
                'test1' => $this->provider1,
                'test2' => $this->provider2,
            ]);

        $this->handler = new TwoFactorProviderHandler($this->providerRegistry);
    }

    private function createUserWithPreferredProvider(string $preferredProvider): MockObject
    {
        $user = $this->createMock(PreferredProviderInterface::class);
        $user
            ->expects($this->any())
            ->method('getPreferredTwoFactorProvider')
            ->willReturn($preferredProvider);

        return $user;
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

        $this->handler->beginTwoFactorAuthentication($context);
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
        $returnValue = $this->handler->beginTwoFactorAuthentication($context);
        $this->assertInstanceOf(TwoFactorToken::class, $returnValue);
        $this->assertSame($originalToken, $returnValue->getAuthenticatedToken());
        $this->assertEquals('firewallName', $returnValue->getProviderKey());
        $this->assertEquals(['test2'], $returnValue->getTwoFactorProviders());
    }

    /**
     * @test
     */
    public function beginAuthentication_noProviderStarts_returnOriginalToken()
    {
        $originalToken = $this->createToken();
        $context = $this->createAuthenticationContext(null, $originalToken);
        $this->stubProvidersReturn(false, false);

        $returnValue = $this->handler->beginTwoFactorAuthentication($context);
        $this->assertSame($originalToken, $returnValue);
    }

    /**
     * @test
     */
    public function beginAuthentication_hasPreferredProvider_setThatProviderFirst()
    {
        $user = $this->createUserWithPreferredProvider('test2');
        $originalToken = $this->createToken();
        $context = $this->createAuthenticationContext(null, $originalToken, $user);
        $this->stubProvidersReturn(true, true);

        /** @var TwoFactorToken $returnValue */
        $returnValue = $this->handler->beginTwoFactorAuthentication($context);
        $this->assertInstanceOf(TwoFactorToken::class, $returnValue);
        $this->assertEquals(['test2', 'test1'], $returnValue->getTwoFactorProviders());
    }

    /**
     * @test
     */
    public function beginAuthentication_invalidPreferredProvider_changeNothing()
    {
        $user = $this->createUserWithPreferredProvider('invalid');
        $originalToken = $this->createToken();
        $context = $this->createAuthenticationContext(null, $originalToken, $user);
        $this->stubProvidersReturn(true, true);

        /** @var TwoFactorToken $returnValue */
        $returnValue = $this->handler->beginTwoFactorAuthentication($context);
        $this->assertInstanceOf(TwoFactorToken::class, $returnValue);
        $this->assertEquals(['test1', 'test2'], $returnValue->getTwoFactorProviders());
    }
}
