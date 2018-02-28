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
    public function beginAuthentication_trustedOptionEnabled_checkTrustedToken()
    {
        $user = $this->createUser();
        $context = $this->createAuthenticationContext(null, null, $user);

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
        $context = $this->createAuthenticationContext(null, $originalToken);
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
        $context = $this->createAuthenticationContext(null, null, $user);
        $this->stubIsTrustedDevice(true);

        $this->trustedDeviceManager
            ->expects($this->once())
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
        $context = $this->createAuthenticationContext(null, null, $user);
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
        $context = $this->createAuthenticationContext();
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
