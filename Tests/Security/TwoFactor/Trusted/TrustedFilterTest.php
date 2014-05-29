<?php
namespace Scheb\TwoFactorBundle\Tests\Security\TwoFactor\Trusted;

use Scheb\TwoFactorBundle\Security\TwoFactor\Trusted\TrustedFilter;
use Scheb\TwoFactorBundle\Security\TwoFactor\AuthenticationContext;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Cookie;

class TrustedFilterTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $registry;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $cookieManager;

    /**
     * @var \Scheb\TwoFactorBundle\Security\TwoFactor\Trusted\TrustedFilter
     */
    private $trustedFilter;

    public function setUp()
    {
        $this->registry = $this->getMockBuilder("Scheb\TwoFactorBundle\Security\TwoFactor\Provider\TwoFactorProviderRegistry")
            ->disableOriginalConstructor()
            ->getMock();

        $this->cookieManager = $this->getMockBuilder("Scheb\TwoFactorBundle\Security\TwoFactor\Trusted\TrustedCookieManager")
            ->disableOriginalConstructor()
            ->getMock();

        $this->trustedFilter = $this->getTrustedFilter($this->registry, $this->cookieManager, true);
    }

    private function getTrustedFilter($registry, $cookieManager, $enableTrustedOption)
    {
        return new TrustedFilter($registry, $cookieManager, $enableTrustedOption);
    }

    private function getRequest()
    {
        $request = $this->getMockBuilder("Symfony\Component\HttpFoundation\Request")
            ->disableOriginalConstructor()
            ->getMock();

        return $request;
    }

    private function getToken($user = null)
    {
        $token = $this->getMock("Symfony\Component\Security\Core\Authentication\Token\TokenInterface");

        $token
            ->expects($this->any())
            ->method("getUser")
            ->will($this->returnValue($user ? $user : $this->getSupportedUser()));

        return $token;
    }

    public function getSupportedUser()
    {
        return $this->getMock("Scheb\TwoFactorBundle\Model\TrustedComputerInterface");
    }

    public function getNotSupportedUser()
    {
        return $this->getMock("Symfony\Component\Security\Core\User\UserInterface");
    }

    public function getResponse()
    {
        $response = new Response();
        $response->headers = $this->getMock("Symfony\Component\HttpFoundation\ResponseHeaderBag");

        return $response;
    }

    public function stubReturnResponseAndSetAuthenticated($response)
    {
        $setAuthenticatedReturnResponse = function ($object) use ($response) {
            $object->setAuthenticated(true); return $response;
        };

        return $this->returnCallback($setAuthenticatedReturnResponse);
    }

    /**
     * @test
     * @dataProvider getTrustedOptionAndUsers
     */
    public function beginAuthentication_trustedOptionNotUsed_notCheckTrustedCookie($trustedOptionEnabled, $user)
    {
        $trustedFilter = $this->getTrustedFilter($this->registry, $this->cookieManager, $trustedOptionEnabled);

        //Mock the TrustedCookieManager
        $this->cookieManager
            ->expects($this->never())
            ->method("isTrustedComputer");

        $request = $this->getRequest();
        $token = $this->getToken($user);
        $trustedFilter->beginAuthentication($request, $token);
    }

    /**
     * Return test data for trustedOption and user object
     * @return array
     */
    public function getTrustedOptionAndUsers()
    {
        $supportedUser = $this->getSupportedUser();
        $unsupportedUser = $this->getNotSupportedUser();

        return array(
            array(false, $unsupportedUser),
            array(true, $unsupportedUser),
            array(false, $supportedUser),
        );
    }

    /**
     * @test
     */
    public function beginAuthentication_trustedOptionUsed_checkTrustedCookie()
    {
        $request = $this->getRequest();
        $user = $this->getSupportedUser();
        $token = $this->getToken($user);

        //Mock the TrustedCookieManager
        $this->cookieManager
            ->expects($this->once())
            ->method("isTrustedComputer")
            ->with($request, $user);

        $this->trustedFilter->beginAuthentication($request, $token);
    }

    /**
     * @test
     */
    public function beginAuthentication_isTrustedComputer_notCallRegistry()
    {
        //Stub the TrustedCookieManager
        $this->cookieManager
            ->expects($this->any())
            ->method("isTrustedComputer")
            ->will($this->returnValue(true));

        //Mock the registry
        $this->registry
            ->expects($this->never())
            ->method("beginAuthentication");

        $request = $this->getRequest();
        $token = $this->getToken();
        $this->trustedFilter->beginAuthentication($request, $token);
    }

    /**
     * @test
     */
    public function beginAuthentication_notTrustedComputer_getResponseFromRegistry()
    {
        $request = $this->getRequest();
        $token = $this->getToken();
        $expectedContext = new AuthenticationContext($request, $token, true);

        //Stub the TrustedCookieManager
        $this->cookieManager
            ->expects($this->any())
            ->method("isTrustedComputer")
            ->will($this->returnValue(false));

        //Mock the registry
        $this->registry
            ->expects($this->once())
            ->method("beginAuthentication")
            ->with($expectedContext);

        $this->trustedFilter->beginAuthentication($request, $token);
    }

    /**
     * @test
     * @dataProvider getResponseAndExpectedReturnValue
     */
    public function requestAuthenticationCode_createNoResponse_returnNull($response)
    {
        $request = $this->getRequest();
        $token = $this->getToken();
        $expectedContext = new AuthenticationContext($request, $token, true);

        //Mock the registry
        $this->registry
            ->expects($this->once())
            ->method("requestAuthenticationCode")
            ->with($expectedContext)
            ->will($this->returnValue($response));

        $returnValue = $this->trustedFilter->requestAuthenticationCode($request, $token);
        $this->assertNull($returnValue);
    }

    /**
     * Return test data for response and expected return value
     * @return array
     */
    public function getResponseAndExpectedReturnValue()
    {
        return array(
            array(null),
            array(new \stdClass()),
        );
    }

    /**
     * @test
     */
    public function requestAuthenticationCode_responseCreated_returnResponse()
    {
        $request = $this->getRequest();
        $token = $this->getToken();
        $expectedContext = new AuthenticationContext($request, $token, true);

        //Mock the registry
        $this->registry
            ->expects($this->once())
            ->method("requestAuthenticationCode")
            ->with($expectedContext)
            ->will($this->returnValue(new Response("<form></form>")));

        $returnValue = $this->trustedFilter->requestAuthenticationCode($request, $token);
        $this->assertInstanceOf("Symfony\Component\HttpFoundation\Response", $returnValue);
        $this->assertEquals("<form></form>", $returnValue->getContent());
    }

    /**
     * @test
     */
    public function requestAuthenticationCode_notAuthenticated_notSetTrustedCookie()
    {
        $request = $this->getRequest();
        $request
            ->expects($this->any())
            ->method("get")
            ->with("_trusted")
            ->will($this->returnValue(true)); //Trusted option checked
        $token = $this->getToken();

        //Mock the TrustedCookieManager
        $this->cookieManager
            ->expects($this->never())
            ->method("createTrustedCookie");

        $this->trustedFilter->requestAuthenticationCode($request, $token);
    }

    /**
     * @test
     */
    public function requestAuthenticationCode_authenticatedTrustedNotChecked_notSetTrustedCookie()
    {
        $request = $this->getRequest();
        $request
            ->expects($this->any())
            ->method("get")
            ->with("_trusted")
            ->will($this->returnValue(false)); //Trusted option not checked
        $token = $this->getToken();

        //Stub the registry
        $response = $this->getResponse();
        $this->registry
            ->expects($this->once())
            ->method("requestAuthenticationCode")
            ->will($this->stubReturnResponseAndSetAuthenticated($response));

        //Mock the TrustedCookieManager
        $this->cookieManager
            ->expects($this->never())
            ->method("createTrustedCookie");

        $this->trustedFilter->requestAuthenticationCode($request, $token);
    }

    /**
     * @test
     */
    public function requestAuthenticationCode_authenticatedAndTrustedChecked_setTrustedCookie()
    {
        $request = $this->getRequest();
        $request
            ->expects($this->any())
            ->method("get")
            ->with("_trusted")
            ->will($this->returnValue(true)); //Trusted option checked
        $user = $this->getSupportedUser();
        $token = $this->getToken($user);

        //Stub the registry
        $response = $this->getResponse();
        $this->registry
            ->expects($this->once())
            ->method("requestAuthenticationCode")
            ->will($this->stubReturnResponseAndSetAuthenticated($response));

        //Mock the TrustedCookieManager
        $cookie = new Cookie("someCookie");
        $this->cookieManager
            ->expects($this->once())
            ->method("createTrustedCookie")
            ->with($request, $user)
            ->will($this->returnValue($cookie));

        //Expect cookie be set in response headers
        $response->headers
            ->expects($this->once())
            ->method("setCookie")
            ->with($cookie);

        $this->trustedFilter->requestAuthenticationCode($request, $token);
    }

}
