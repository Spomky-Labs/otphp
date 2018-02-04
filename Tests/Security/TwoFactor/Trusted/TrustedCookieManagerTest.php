<?php

namespace Scheb\TwoFactorBundle\Tests\Security\TwoFactor\Trusted;

use PHPUnit\Framework\MockObject\MockObject;
use Scheb\TwoFactorBundle\Model\TrustedComputerInterface;
use Scheb\TwoFactorBundle\Security\TwoFactor\Trusted\TrustedComputerManagerInterface;
use Scheb\TwoFactorBundle\Security\TwoFactor\Trusted\TrustedCookieManager;
use Scheb\TwoFactorBundle\Security\TwoFactor\Trusted\TrustedTokenGenerator;
use Scheb\TwoFactorBundle\Tests\TestCase;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;

class TrustedCookieManagerTest extends TestCase
{
    /**
     * The DateTime used as "current time" in this test.
     *
     * @var \DateTime
     */
    private $testTime;

    /**
     * @var MockObject|TrustedComputerManagerInterface
     */
    private $trustedComputerManager;

    /**
     * @var MockObject|TrustedTokenGenerator
     */
    private $tokenGenerator;

    /**
     * @var TrustedCookieManager
     */
    private $cookieManager;

    protected function setUp()
    {
        $this->trustedComputerManager = $this->createMock(TrustedComputerManagerInterface::class);

        $this->tokenGenerator = $this->createMock(TrustedTokenGenerator::class);

        $this->cookieManager = new TestableTrustedCookieManager($this->tokenGenerator, $this->trustedComputerManager, 'cookieName', 600, true, 'strict');
        $this->testTime = new \DateTime('2014-01-01 00:00:00 UTC');
        $this->cookieManager->testTime = $this->testTime;
    }

    /**
     * @return MockObject|Request
     */
    private function createRequest($cookieValue = null)
    {
        $request = $this->createMock(Request::class);
        $request->cookies = $this->createMock(ParameterBag::class);

        $request->cookies
            ->expects($this->any())
            ->method('get')
            ->with('cookieName')
            ->willReturn($cookieValue);

        $request->cookies
            ->expects($this->any())
            ->method('has')
            ->with('cookieName')
            ->willReturn($cookieValue ? true : false);

        $request
            ->expects($this->any())
            ->method('getHost')
            ->willReturn("hostname.tld");

        return $request;
    }

    /**
     * @test
     */
    public function isTrustedComputer_noCookieSet_returnFalse()
    {
        $user = $this->createMock(TrustedComputerInterface::class);
        $request = $this->createRequest(null);

        $returnValue = $this->cookieManager->isTrustedComputer($request, $user);
        $this->assertFalse($returnValue);
    }

    /**
     * @test
     */
    public function isTrustedComputer_cookieSet_validateTrustedCodes()
    {
        $user = $this->createMock(TrustedComputerInterface::class);
        $request = $this->createRequest('trustedCode1;trustedCode2');

        //Mock the TrustedComputerManager object
        $this->trustedComputerManager
            ->expects($this->at(0))
            ->method('isTrustedComputer')
            ->with($user, 'trustedCode1');
        $this->trustedComputerManager
            ->expects($this->at(1))
            ->method('isTrustedComputer')
            ->with($user, 'trustedCode2');

        $this->cookieManager->isTrustedComputer($request, $user);
    }

    /**
     * @test
     */
    public function isTrustedComputer_validTrustedCode_returnTrue()
    {
        $user = $this->createMock(TrustedComputerInterface::class);
        $request = $this->createRequest('trustedCode1;trustedCode2');

        //Stub the TrustedComputerManager object
        $this->trustedComputerManager
            ->expects($this->any())
            ->method('isTrustedComputer')
            ->willReturn(true);

        $returnValue = $this->cookieManager->isTrustedComputer($request, $user);
        $this->assertTrue($returnValue);
    }

    /**
     * @test
     */
    public function createTrustedCookie_cookieNotSet_createNewCookie()
    {
        $user = $this->createMock(TrustedComputerInterface::class);
        $request = $this->createRequest(null);

        //Stub the TrustedTokenGenerator
        $this->tokenGenerator
            ->expects($this->any())
            ->method('generateToken')
            ->willReturn('newTrustedCode');

        $returnValue = $this->cookieManager->createTrustedCookie($request, $user);

        //Validate return value
        $validUntil = new \DateTime('2014-01-01 00:10:00 UTC');
        $expectedCookie = new Cookie('cookieName', 'newTrustedCode', $validUntil, '/', '.hostname.tld', true, true, false, 'strict');
        $this->assertEquals($expectedCookie, $returnValue);
    }

    /**
     * @test
     */
    public function createTrustedCookie_cookieIsSet_appendToken()
    {
        $user = $this->createMock(TrustedComputerInterface::class);
        $request = $this->createRequest('trustedCode1');

        //Stub the TrustedTokenGenerator
        $this->tokenGenerator
            ->expects($this->any())
            ->method('generateToken')
            ->willReturn('newTrustedCode');

        $returnValue = $this->cookieManager->createTrustedCookie($request, $user);

        //Validate return value
        $validUntil = new \DateTime('2014-01-01 00:10:00 UTC');
        $expectedCookie = new Cookie('cookieName', 'trustedCode1;newTrustedCode', $validUntil, '/', '.hostname.tld', true, true, false, 'strict');
        $this->assertEquals($expectedCookie, $returnValue);
    }

    /**
     * @test
     */
    public function createTrustedCookie_newTrustedToken_persistUserEntity()
    {
        $user = $this->createMock(TrustedComputerInterface::class);
        $request = $this->createRequest();

        //Stub the TrustedTokenGenerator
        $this->tokenGenerator
            ->expects($this->once())
            ->method('generateToken')
            ->willReturn('newTrustedCode');

        //Mock the TrustedComputerManager object
        $this->trustedComputerManager
            ->expects($this->once())
            ->method('addTrustedComputer')
            ->with($user, 'newTrustedCode');

        $this->cookieManager->createTrustedCookie($request, $user);
    }

    /**
     * @test
     */
    public function createTrustedCookie_localhostSkippedInCookie()
    {
        $request = Request::create('');
        $user = $this->createMock(TrustedComputerInterface::class);

        $cookie = $this->cookieManager->createTrustedCookie($request, $user);

        $this->assertNull($cookie->getDomain());
    }
}

/**
 * Make the TrustedCookieManager testable.
 */
class TestableTrustedCookieManager extends TrustedCookieManager
{
    public $testTime;

    protected function getDateTimeNow(): \DateTime
    {
        return clone $this->testTime;
    }
}
