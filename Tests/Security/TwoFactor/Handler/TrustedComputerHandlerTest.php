<?php

namespace Scheb\TwoFactorBundle\Tests\Security\TwoFactor\Handler;

use PHPUnit\Framework\MockObject\MockObject;
use Scheb\TwoFactorBundle\Security\TwoFactor\Handler\AuthenticationHandlerInterface;
use Scheb\TwoFactorBundle\Security\TwoFactor\Handler\TrustedComputerHandler;
use Scheb\TwoFactorBundle\Security\TwoFactor\Trusted\TrustedComputerManager;

class TrustedComputerHandlerTest extends AuthenticationHandlerTestCase
{
    /**
     * @var MockObject|AuthenticationHandlerInterface
     */
    private $innerAuthenticationHandler;

    /**
     * @var MockObject|TrustedComputerManager
     */
    private $trustedComputerManager;

    /**
     * @var TrustedComputerHandler
     */
    private $trustedHandler;

    protected function setUp()
    {
        $this->innerAuthenticationHandler = $this->createMock(AuthenticationHandlerInterface::class);
        $this->trustedComputerManager = $this->createMock(TrustedComputerManager::class);
        $this->trustedHandler = $this->createTrustedHandler(false);
    }

    private function createTrustedHandler(bool $extendTrustedToken): TrustedComputerHandler
    {
        return new TrustedComputerHandler($this->innerAuthenticationHandler, $this->trustedComputerManager, $extendTrustedToken);
    }

    private function createAuthenticationContextWithTrustedOption(bool $useTrustedOption, $request = null, $token = null, $user = null)
    {
        $context = parent::createAuthenticationContext($request, $token, $user);
        $context
            ->expects($this->once())
            ->method('useTrustedOption')
            ->willReturn($useTrustedOption);

        return $context;
    }

    protected function stubIsTrustedComputer(bool $isTrustedComputer): void
    {
        $this->trustedComputerManager
            ->expects($this->any())
            ->method('isTrustedComputer')
            ->willReturn($isTrustedComputer);
    }

    /**
     * @test
     */
    public function beginAuthentication_trustedOptionDisabled_returnTokenFromInnerAuthenticationHandler()
    {
        $context = $this->createAuthenticationContextWithTrustedOption(false);
        $transformedToken = $this->createToken();

        $this->trustedComputerManager
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
    public function beginAuthentication_trustedOptionEnabled_checkTrustedToken()
    {
        $user = $this->createUser();
        $context = $this->createAuthenticationContextWithTrustedOption(true, null, null, $user);

        $this->trustedComputerManager
            ->expects($this->once())
            ->method('isTrustedComputer')
            ->with($user, 'firewallName');

        $this->trustedHandler->beginTwoFactorAuthentication($context);
    }

    /**
     * @test
     */
    public function beginAuthentication_isTrustedComputer_returnOriginalToken()
    {
        $originalToken = $this->createToken();
        $context = $this->createAuthenticationContextWithTrustedOption(true,null, $originalToken);
        $this->stubIsTrustedComputer(true);

        $this->innerAuthenticationHandler
            ->expects($this->never())
            ->method($this->anything());

        $returnValue = $this->trustedHandler->beginTwoFactorAuthentication($context);
        $this->assertSame($originalToken, $returnValue);
    }

    /**
     * @test
     */
    public function beginAuthentication_isTrustedComputerAndExtendTrustedToken_addNewTrustedToken()
    {
        $trustedHandler = $this->createTrustedHandler(true);
        $user = $this->createUser();
        $context = $this->createAuthenticationContextWithTrustedOption(true,null, null, $user);
        $this->stubIsTrustedComputer(true);

        $this->trustedComputerManager
            ->expects($this->any())
            ->method('addTrustedComputer')
            ->willReturn($user, 'firewallName');

        $trustedHandler->beginTwoFactorAuthentication($context);
    }

    /**
     * @test
     */
    public function beginAuthentication_isTrustedComputerAndNotExtendTrustedToken_notAddNewTrustedToken()
    {
        $trustedHandler = $this->createTrustedHandler(false);
        $user = $this->createUser();
        $context = $this->createAuthenticationContextWithTrustedOption(true, null, null, $user);
        $this->stubIsTrustedComputer(true);

        $this->trustedComputerManager
            ->expects($this->never())
            ->method('addTrustedComputer');

        $trustedHandler->beginTwoFactorAuthentication($context);
    }

    /**
     * @test
     */
    public function beginAuthentication_notTrustedComputer_returnTokenFromInnerAuthenticationHandler()
    {
        $context = $this->createAuthenticationContextWithTrustedOption(true);
        $transformedToken = $this->createToken();
        $this->stubIsTrustedComputer(false);

        $this->trustedComputerManager
            ->expects($this->never())
            ->method('addTrustedComputer');

        $this->innerAuthenticationHandler
            ->expects($this->once())
            ->method('beginTwoFactorAuthentication')
            ->with($context)
            ->willReturn($transformedToken);

        $returnValue = $this->trustedHandler->beginTwoFactorAuthentication($context);
        $this->assertSame($transformedToken, $returnValue);
    }
}
