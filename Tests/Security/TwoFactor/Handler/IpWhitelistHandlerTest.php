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
        $ipWhitelist = [
            '127.0.0.1',
            '192.168.0.0/16',
            '2001:0db8:85a3:0000:0000:8a2e:0370:7334',
            '2001:db8:abcd:0012::0/64',
        ];
        $this->ipWhitelistHandler = new IpWhitelistHandler($this->innerAuthenticationHandler, $ipWhitelist);
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
     * @dataProvider getWhitelistedIps
     */
    public function beginTwoFactorAuthentication_ipIsWhitelisted_returnSameToken(string $ip)
    {
        $request = $this->createRequestWithIp($ip);
        $originalToken = $this->createToken();
        $authenticationContext = $this->createAuthenticationContext($request, $originalToken);

        $this->innerAuthenticationHandler
            ->expects($this->never())
            ->method($this->anything());

        $returnValue = $this->ipWhitelistHandler->beginTwoFactorAuthentication($authenticationContext);
        $this->assertSame($originalToken, $returnValue);
    }

    public function getWhitelistedIps(): array
    {
        return [
            ['127.0.0.1'],
            ['192.168.0.1'],
            ['2001:0db8:85a3:0000:0000:8a2e:0370:7334'],
            ['2001:db8:abcd:0012:0000:0000:0000:0001'],
        ];
    }

    /**
     * @test
     */
    public function beginTwoFactorAuthentication_ipNotWhitelisted_returnTokenFromInnerAuthenticationHandler()
    {
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
