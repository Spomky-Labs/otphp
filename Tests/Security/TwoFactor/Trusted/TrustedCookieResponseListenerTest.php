<?php

namespace Scheb\TwoFactorBundle\Tests\Security\TwoFactor\Trusted;

use PHPUnit\Framework\MockObject\MockObject;
use Scheb\TwoFactorBundle\Security\TwoFactor\Trusted\TrustedCookieResponseListener;
use Scheb\TwoFactorBundle\Security\TwoFactor\Trusted\TrustedDeviceTokenStorage;
use Scheb\TwoFactorBundle\Tests\TestCase;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;

class TrustedCookieResponseListenerTest extends TestCase
{
    /**
     * @var MockObject|TrustedDeviceTokenStorage
     */
    private $trustedTokenStorage;

    /**
     * @var TrustedCookieResponseListener
     */
    private $cookieResponseListener;

    /**
     * @var MockObject|FilterResponseEvent
     */
    private $event;

    /**
     * @var Response
     */
    private $response;

    protected function setUp()
    {
        $this->trustedTokenStorage = $this->createMock(TrustedDeviceTokenStorage::class);
        $this->cookieResponseListener = new TestableTrustedCookieResponseListener($this->trustedTokenStorage,
            3600, 'cookieName', true, Cookie::SAMESITE_LAX);
        $this->cookieResponseListener->now = new \DateTime('2018-01-01 00:00:00');

        // Create a testable FilterResponseEvent
        $request = $this->createMock(Request::class);
        $request
            ->expects($this->any())
            ->method('getHost')
            ->willReturn('example.org');
        $this->response = new Response();
        $this->event = $this->createMock(FilterResponseEvent::class);
        $this->event
            ->expects($this->any())
            ->method('getRequest')
            ->willReturn($request);
        $this->event
            ->expects($this->any())
            ->method('getResponse')
            ->willReturn($this->response);
    }

    /**
     * @test
     */
    public function onKernelResponse_noUpdatedCookie_noCookieHeader()
    {
        $this->trustedTokenStorage
            ->expects($this->once())
            ->method('hasUpdatedCookie')
            ->willReturn(false);

        $this->cookieResponseListener->onKernelResponse($this->event);
        $this->assertCount(0, $this->response->headers->getCookies(), 'Response must have no cookie set.');
    }

    /**
     * @test
     */
    public function onKernelResponse_hasUpdatedCookie_addCookieHeader()
    {
        $this->trustedTokenStorage
            ->expects($this->once())
            ->method('hasUpdatedCookie')
            ->willReturn(true);

        $this->trustedTokenStorage
            ->expects($this->once())
            ->method('getCookieValue')
            ->willReturn('cookieValue');

        $this->cookieResponseListener->onKernelResponse($this->event);
        $cookies = $this->response->headers->getCookies();
        $this->assertCount(1, $cookies, 'Response must have a cookie set.');

        $expectedCookie = new Cookie(
            'cookieName',
            'cookieValue',
            new \DateTime('2018-01-01 01:00:00'),
            '/',
            '.example.org',
            true,
            true,
            false,
            Cookie::SAMESITE_LAX
        );
        $this->assertEquals($expectedCookie, $cookies[0]);
    }
}

// Make the current DateTime testable
class TestableTrustedCookieResponseListener extends TrustedCookieResponseListener
{
    public $now;

    protected function getDateTimeNow(): \DateTime
    {
        return $this->now;
    }
}
