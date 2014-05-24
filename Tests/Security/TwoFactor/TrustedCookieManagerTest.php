<?php
namespace Scheb\TwoFactorBundle\Tests\Security\TwoFactor;

use Scheb\TwoFactorBundle\Security\TwoFactor\TrustedCookieManager;
use Symfony\Component\HttpFoundation\Cookie;

class TrustedCookieManagerTest extends \PHPUnit_Framework_TestCase
{

    /**
     * The DateTime used as "current time" in this test
     * @var \DateTime
     */
    private $testTime;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $em;

    /**
     * @var \Scheb\TwoFactorBundle\Security\TwoFactor\TrustedCookieManager
     */
    private $cookieManager;

    public function setUp()
    {
        $this->em = $this->getMockBuilder("Doctrine\ORM\EntityManager")
            ->setMethods(array("persist", "flush"))
            ->disableOriginalConstructor()
            ->getMock();

        $this->cookieManager = new TestableTrustedCookieManager($this->em, "cookieName", 600);
        $this->testTime = new \DateTime("2014-01-01 00:00:00 UTC");
        $this->cookieManager->testTime = $this->testTime;
        $this->cookieManager->token = "newTrustedCode";
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
            ->method("get")
            ->with("cookieName")
            ->will($this->returnValue($cookieValue));

        $request->cookies
            ->expects($this->any())
            ->method("has")
            ->with("cookieName")
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
        $request = $this->createRequest("trustedCode1;trustedCode2");

        //Mock the User object
        $user
            ->expects($this->at(0))
            ->method("isTrustedComputer")
            ->with("trustedCode1");
        $user
            ->expects($this->at(1))
            ->method("isTrustedComputer")
            ->with("trustedCode2");

        $this->cookieManager->isTrustedComputer($request, $user);
    }

    /**
     * @test
     */
    public function isTrustedComputer_validTrustedCode_returnTrue()
    {
        $user = $this->getMock("Scheb\TwoFactorBundle\Model\TrustedComputerInterface");
        $request = $this->createRequest("trustedCode1;trustedCode2");

        //Stub the User object
        $user
            ->expects($this->any())
            ->method("isTrustedComputer")
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

        $returnValue = $this->cookieManager->createTrustedCookie($request, $user);

        //Validate return value
        $validUntil = new \DateTime("2014-01-01 00:10:00 UTC");
        $expectedCookie = new Cookie("cookieName", "newTrustedCode", $validUntil, "/");
        $this->assertEquals($expectedCookie, $returnValue);
    }

    /**
     * @test
     */
    public function createTrustedCookie_cookieIsSet_appendToken()
    {
        $user = $this->getMock("Scheb\TwoFactorBundle\Model\TrustedComputerInterface");
        $request = $this->createRequest("trustedCode1");

        $returnValue = $this->cookieManager->createTrustedCookie($request, $user);

        //Validate return value
        $validUntil = new \DateTime("2014-01-01 00:10:00 UTC");
        $expectedCookie = new Cookie("cookieName", "trustedCode1;newTrustedCode", $validUntil, "/");
        $this->assertEquals($expectedCookie, $returnValue);
    }

    /**
     * @test
     */
    public function createTrustedCookie_newTrustedToken_persistUserEntity()
    {
        $user = $this->getMock("Scheb\TwoFactorBundle\Model\TrustedComputerInterface");
        $request = $this->createRequest();

        //Mock the User object
        $user
            ->expects($this->once())
            ->method("addTrustedComputer")
            ->with("newTrustedCode");

        //Mock the EntityManager
        $this->em
            ->expects($this->once())
            ->method("persist")
            ->with($user);
        $this->em
            ->expects($this->once())
            ->method("flush");

        $this->cookieManager->createTrustedCookie($request, $user);
    }

}

/**
 * Make the TrustedCookieManager testable
 */
class TestableTrustedCookieManager extends TrustedCookieManager
{
    public $token;
    public $testTime;

    protected function getDateTimeNow()
    {
        return clone $this->testTime;
    }

    protected function generateToken()
    {
        return $this->token;
    }

}
