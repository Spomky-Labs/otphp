<?php

namespace Scheb\TwoFactorBundle\Tests\Security\TwoFactor\Handler;

use PHPUnit\Framework\MockObject\MockObject;
use Scheb\TwoFactorBundle\Security\TwoFactor\Handler\AuthenticationHandlerInterface;
use Scheb\TwoFactorBundle\Security\TwoFactor\Handler\IpWhitelistHandler;

class IpWhitelistHandlerTest extends AuthenticationHandlerTestCase
{
    /**
     * @var MockObject|AuthenticationHandlerInterface
     */
    private $innerAuthenticationHandler;

    /**
     * @var IpWhitelistHandler
     */
    private $ipWhitelistHandler;

    protected function setUp()
    {
        $this->innerAuthenticationHandler = $this->getAuthenticationHandlerMock();
        $this->ipWhitelistHandler = new IpWhitelistHandler($this->innerAuthenticationHandler, ['127.0.0.1']);
    }

    private function createRequestWithIp($ip)
    {
        $request = $this->createRequest();
        $request
            ->expects($this->once())
            ->method('getClientIp')
            ->willReturn($ip);
        return $request;
    }

    /**
     * @test
     */
    public function beginTwoFactorAuthentication_ipIsWhitelisted_returnSameToken() {
        $request = $this->createRequestWithIp('127.0.0.1');
        $originalToken = $this->createToken();
        $authenticationContext = $this->createAuthenticationContext($request, $originalToken);

        $this->innerAuthenticationHandler
            ->expects($this->never())
            ->method($this->anything());

        $returnValue = $this->ipWhitelistHandler->beginTwoFactorAuthentication($authenticationContext);
        $this->assertSame($originalToken, $returnValue);
    }

    /**
     * @test
     */
    public function beginTwoFactorAuthentication_ipNotWhitelisted_returnTokenFromInnerAuthenticationHandler() {
        $request = $this->createRequestWithIp('1.1.1.1');
        $transformedToken = $this->createToken();
        $authenticationContext = $this->createAuthenticationContext($request);

        $this->innerAuthenticationHandler
            ->expects($this->once())
            ->method('beginTwoFactorAuthentication')
            ->with($authenticationContext)
            ->willReturn($transformedToken);

        $returnValue = $this->ipWhitelistHandler->beginTwoFactorAuthentication($authenticationContext);
        $this->assertSame($transformedToken, $returnValue);
    }
}
