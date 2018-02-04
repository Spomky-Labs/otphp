<?php

namespace Scheb\TwoFactorBundle\Tests\Security\TwoFactor\Handler;

use PHPUnit\Framework\MockObject\MockObject;
use Scheb\TwoFactorBundle\Security\TwoFactor\Handler\AuthenticationHandlerInterface;
use Scheb\TwoFactorBundle\Security\TwoFactor\Handler\TrustedComputerHandler;
use Scheb\TwoFactorBundle\Security\TwoFactor\Trusted\TrustedCookieManager;

class TrustedComputerHandlerTest extends AuthenticationHandlerTestCase
{
    /**
     * @var MockObject|AuthenticationHandlerInterface
     */
    private $innerAuthenticationHandler;

    /**
     * @var MockObject|TrustedCookieManager
     */
    private $cookieManager;

    /**
     * @var TrustedComputerHandler
     */
    private $trustedHandler;

    public function setUp()
    {
        $this->innerAuthenticationHandler = $this->createMock(AuthenticationHandlerInterface::class);
        $this->cookieManager = $this->createMock(TrustedCookieManager::class);
        $this->trustedHandler = new TrustedComputerHandler($this->innerAuthenticationHandler, $this->cookieManager, 'trustedName');
    }

    private function stubUseTrustedOption(MockObject $context, bool $useTrustedOption): void
    {
        $context
            ->expects($this->once())
            ->method('useTrustedOption')
            ->willReturn($useTrustedOption);
    }

    /**
     * @test
     */
    public function beginAuthentication_trustedOptionDisabled_returnTokenFromInnerAuthenticationHandler()
    {
        $context = $this->createAuthenticationContext();
        $transformedToken = $this->createToken();
        $this->stubUseTrustedOption($context, false);

        $this->cookieManager
            ->expects($this->never())
            ->method($this->anything());

        $this->innerAuthenticationHandler
            ->expects($this->once())
            ->method('beginTwoFactorAuthentication')
            ->willReturn($transformedToken);

        $returnValue = $this->trustedHandler->beginTwoFactorAuthentication($context);
        $this->assertSame($transformedToken, $returnValue);
    }

    /**
     * @test
     */
    public function beginAuthentication_trustedOptionEnabled_checkTrustedCookie()
    {
        $request = $this->createRequest();
        $user = $this->createUser();
        $context = $this->createAuthenticationContext();
        $this->stubUseTrustedOption($context, true);

        $this->cookieManager
            ->expects($this->once())
            ->method('isTrustedComputer')
            ->with($request, $user);

        $this->trustedHandler->beginTwoFactorAuthentication($context);
    }

    /**
     * @test
     */
    public function beginAuthentication_isTrustedComputer_returnOriginalToken()
    {
        $originalToken = $this->createToken();
        $context = $this->createAuthenticationContext(null, $originalToken);
        $this->stubUseTrustedOption($context, true);

        $this->cookieManager
            ->expects($this->any())
            ->method('isTrustedComputer')
            ->willReturn(true);

        $this->innerAuthenticationHandler
            ->expects($this->never())
            ->method($this->anything());

        $returnValue = $this->trustedHandler->beginTwoFactorAuthentication($context);
        $this->assertSame($originalToken, $returnValue);
    }

    /**
     * @test
     */
    public function beginAuthentication_notTrustedComputer_returnTokenFromInnerAuthenticationHandler()
    {
        $context = $this->createAuthenticationContext();
        $transformedToken = $this->createToken();

        $this->cookieManager
            ->expects($this->any())
            ->method('isTrustedComputer')
            ->willReturn(false);

        $this->innerAuthenticationHandler
            ->expects($this->once())
            ->method('beginTwoFactorAuthentication')
            ->with($context)
            ->willReturn($transformedToken);

        $returnValue = $this->trustedHandler->beginTwoFactorAuthentication($context);
        $this->assertSame($transformedToken, $returnValue);
    }
}
