<?php

namespace Scheb\TwoFactorBundle\Tests\Security\TwoFactor\Handler;

use PHPUnit\Framework\MockObject\MockObject;
use Scheb\TwoFactorBundle\Security\TwoFactor\Handler\AuthenticationHandlerInterface;
use Scheb\TwoFactorBundle\Security\TwoFactor\Handler\TrustedDeviceHandler;
use Scheb\TwoFactorBundle\Security\TwoFactor\Trusted\TrustedDeviceManager;

class TrustedDeviceHandlerTest extends AuthenticationHandlerTestCase
{
    /**
     * @var MockObject|AuthenticationHandlerInterface
     */
    private $innerAuthenticationHandler;

    /**
     * @var MockObject|TrustedDeviceManager
     */
    private $trustedDeviceManager;

    /**
     * @var TrustedDeviceHandler
     */
    private $trustedHandler;

    protected function setUp()
    {
        $this->innerAuthenticationHandler = $this->createMock(AuthenticationHandlerInterface::class);
        $this->trustedDeviceManager = $this->createMock(TrustedDeviceManager::class);
        $this->trustedHandler = $this->createTrustedHandler(false);
    }

    private function createTrustedHandler(bool $extendTrustedToken): TrustedDeviceHandler
    {
        return new TrustedDeviceHandler($this->innerAuthenticationHandler, $this->trustedDeviceManager, $extendTrustedToken);
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

    protected function stubIsTrustedDevice(bool $isTrustedDevice): void
    {
        $this->trustedDeviceManager
            ->expects($this->any())
            ->method('isTrustedDevice')
            ->willReturn($isTrustedDevice);
    }

    /**
     * @test
     */
    public function beginAuthentication_trustedOptionDisabled_returnTokenFromInnerAuthenticationHandler()
    {
        $context = $this->createAuthenticationContextWithTrustedOption(false);
        $transformedToken = $this->createToken();

        $this->trustedDeviceManager
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

        $this->trustedDeviceManager
            ->expects($this->once())
            ->method('isTrustedDevice')
            ->with($user, 'firewallName');

        $this->trustedHandler->beginTwoFactorAuthentication($context);
    }

    /**
     * @test
     */
    public function beginAuthentication_isTrustedDevice_returnOriginalToken()
    {
        $originalToken = $this->createToken();
        $context = $this->createAuthenticationContextWithTrustedOption(true,null, $originalToken);
        $this->stubIsTrustedDevice(true);

        $this->innerAuthenticationHandler
            ->expects($this->never())
            ->method($this->anything());

        $returnValue = $this->trustedHandler->beginTwoFactorAuthentication($context);
        $this->assertSame($originalToken, $returnValue);
    }

    /**
     * @test
     */
    public function beginAuthentication_isTrustedDeviceAndExtendTrustedToken_addNewTrustedToken()
    {
        $trustedHandler = $this->createTrustedHandler(true);
        $user = $this->createUser();
        $context = $this->createAuthenticationContextWithTrustedOption(true,null, null, $user);
        $this->stubIsTrustedDevice(true);

        $this->trustedDeviceManager
            ->expects($this->any())
            ->method('addTrustedDevice')
            ->willReturn($user, 'firewallName');

        $trustedHandler->beginTwoFactorAuthentication($context);
    }

    /**
     * @test
     */
    public function beginAuthentication_isTrustedDeviceAndNotExtendTrustedToken_notAddNewTrustedToken()
    {
        $trustedHandler = $this->createTrustedHandler(false);
        $user = $this->createUser();
        $context = $this->createAuthenticationContextWithTrustedOption(true, null, null, $user);
        $this->stubIsTrustedDevice(true);

        $this->trustedDeviceManager
            ->expects($this->never())
            ->method('addTrustedDevice');

        $trustedHandler->beginTwoFactorAuthentication($context);
    }

    /**
     * @test
     */
    public function beginAuthentication_notTrustedDevice_returnTokenFromInnerAuthenticationHandler()
    {
        $context = $this->createAuthenticationContextWithTrustedOption(true);
        $transformedToken = $this->createToken();
        $this->stubIsTrustedDevice(false);

        $this->trustedDeviceManager
            ->expects($this->never())
            ->method('addTrustedDevice');

        $this->innerAuthenticationHandler
            ->expects($this->once())
            ->method('beginTwoFactorAuthentication')
            ->with($context)
            ->willReturn($transformedToken);

        $returnValue = $this->trustedHandler->beginTwoFactorAuthentication($context);
        $this->assertSame($transformedToken, $returnValue);
    }
}
