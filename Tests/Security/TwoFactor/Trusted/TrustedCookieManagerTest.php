<?php

namespace Scheb\TwoFactorBundle\Tests\Security\TwoFactor\Trusted;

use Scheb\TwoFactorBundle\Security\TwoFactor\Trusted\TrustedCookieManager;
use Symfony\Component\HttpFoundation\Cookie;

class TrustedCookieManagerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * The DateTime used as "current time" in this test.
     *
     * @var \DateTime
     */
    private $testTime;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $persister;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $tokenGenerator;

    /**
     * @var TrustedCookieManager
     */
    private $cookieManager;

    public function setUp()
    {
        $this->persister = $this->getMock("Scheb\TwoFactorBundle\Model\PersisterInterface");

        $this->tokenGenerator = $this->getMock("Scheb\TwoFactorBundle\Security\TwoFactor\Trusted\TrustedTokenGenerator");

        $this->cookieManager = new TestableTrustedCookieManager($this->persister, $this->tokenGenerator, 'cookieName', 600);
        $this->testTime = new \DateTime('2014-01-01 00:00:00 UTC');
        $this->cookieManager->testTime = $this->testTime;
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function createRequest($cookieValue = null)
    {
        $request = $this->getMock("Symfony\Component\HttpFoundation\Request");
        $request->cookies = $this->getMock("Symfony\Component\HttpFoundation\ParameterBag");

        $request->cookies
            ->expects($this->any())
            ->method('get')
            ->with('cookieName')
            ->will($this->returnValue($cookieValue));

        $request->cookies
            ->expects($this->any())
            ->method('has')
            ->with('cookieName')
            ->will($this->returnValue($cookieValue ? true : false));

        return $request;
    }

    /**
     * @test
     */
    public function isTrustedComputer_noCookieSet_returnFalse()
    {
        $user = $this->getMock("Scheb\TwoFactorBundle\Model\TrustedComputerInterface");
        $request = $this->createRequest(null);

        $returnValue = $this->cookieManager->isTrustedComputer($request, $user);
        $this->assertFalse($returnValue);
    }

    /**
     * @test
     */
    public function isTrustedComputer_cookieSet_validateTrustedCodes()
    {
        $user = $this->getMock("Scheb\TwoFactorBundle\Model\TrustedComputerInterface");
        $request = $this->createRequest('trustedCode1;trustedCode2');

        //Mock the User object
        $user
            ->expects($this->at(0))
            ->method('isTrustedComputer')
            ->with('trustedCode1');
        $user
            ->expects($this->at(1))
            ->method('isTrustedComputer')
            ->with('trustedCode2');

        $this->cookieManager->isTrustedComputer($request, $user);
    }

    /**
     * @test
     */
    public function isTrustedComputer_validTrustedCode_returnTrue()
    {
        $user = $this->getMock("Scheb\TwoFactorBundle\Model\TrustedComputerInterface");
        $request = $this->createRequest('trustedCode1;trustedCode2');

        //Stub the User object
        $user
            ->expects($this->any())
            ->method('isTrustedComputer')
            ->will($this->returnValue(true));

        $returnValue = $this->cookieManager->isTrustedComputer($request, $user);
        $this->assertTrue($returnValue);
    }

    /**
     * @test
     */
    public function createTrustedCookie_CookieNotSet_createNewCookie()
    {
        $user = $this->getMock("Scheb\TwoFactorBundle\Model\TrustedComputerInterface");
        $request = $this->createRequest(null);

        //Stub the TrustedTokenGenerator
        $this->tokenGenerator
            ->expects($this->any())
            ->method('generateToken')
            ->will($this->returnValue('newTrustedCode'));

        $returnValue = $this->cookieManager->createTrustedCookie($request, $user);

        //Validate return value
        $validUntil = new \DateTime('2014-01-01 00:10:00 UTC');
        $expectedCookie = new Cookie('cookieName', 'newTrustedCode', $validUntil, '/');
        $this->assertEquals($expectedCookie, $returnValue);
    }

    /**
     * @test
     */
    public function createTrustedCookie_cookieIsSet_appendToken()
    {
        $user = $this->getMock("Scheb\TwoFactorBundle\Model\TrustedComputerInterface");
        $request = $this->createRequest('trustedCode1');

        //Stub the TrustedTokenGenerator
        $this->tokenGenerator
            ->expects($this->any())
            ->method('generateToken')
            ->will($this->returnValue('newTrustedCode'));

        $returnValue = $this->cookieManager->createTrustedCookie($request, $user);

        //Validate return value
        $validUntil = new \DateTime('2014-01-01 00:10:00 UTC');
        $expectedCookie = new Cookie('cookieName', 'trustedCode1;newTrustedCode', $validUntil, '/');
        $this->assertEquals($expectedCookie, $returnValue);
    }

    /**
     * @test
     */
    public function createTrustedCookie_newTrustedToken_persistUserEntity()
    {
        $user = $this->getMock("Scheb\TwoFactorBundle\Model\TrustedComputerInterface");
        $request = $this->createRequest();

        //Stub the TrustedTokenGenerator
        $this->tokenGenerator
            ->expects($this->once())
            ->method('generateToken')
            ->will($this->returnValue('newTrustedCode'));

        //Mock the User object
        $user
            ->expects($this->once())
            ->method('addTrustedComputer')
            ->with('newTrustedCode');

        //Mock the persister
        $this->persister
            ->expects($this->once())
            ->method('persist')
            ->with($user);

        $this->cookieManager->createTrustedCookie($request, $user);
    }
}

/**
 * Make the TrustedCookieManager testable.
 */
class TestableTrustedCookieManager extends TrustedCookieManager
{
    public $testTime;

    protected function getDateTimeNow()
    {
        return clone $this->testTime;
    }
}
