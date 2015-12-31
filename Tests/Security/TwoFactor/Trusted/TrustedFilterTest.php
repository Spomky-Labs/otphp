<?php

namespace Scheb\TwoFactorBundle\Tests\Security\TwoFactor\Trusted;

use Scheb\TwoFactorBundle\Security\TwoFactor\Trusted\TrustedFilter;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Cookie;

class TrustedFilterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $authHandler;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $cookieManager;

    /**
     * @var TrustedFilter
     */
    private $trustedFilter;

    public function setUp()
    {
        $this->authHandler = $this->getMock("Scheb\TwoFactorBundle\Security\TwoFactor\AuthenticationHandlerInterface");

        $this->cookieManager = $this->getMockBuilder("Scheb\TwoFactorBundle\Security\TwoFactor\Trusted\TrustedCookieManager")
            ->disableOriginalConstructor()
            ->getMock();

        $this->trustedFilter = $this->getTrustedFilter($this->authHandler, $this->cookieManager, true);
    }

    private function getTrustedFilter($authHandler, $cookieManager, $enableTrustedOption)
    {
        return new TrustedFilter($authHandler, $cookieManager, $enableTrustedOption, 'trustedName');
    }

    public function getAuthenticationContext($request = null, $user = null)
    {
        $context = $this->getMockBuilder("Scheb\TwoFactorBundle\Security\TwoFactor\AuthenticationContext")
            ->disableOriginalConstructor()
            ->getMock();

        $context
            ->expects($this->any())
            ->method('getRequest')
            ->will($this->returnValue($request ? $request : $this->getRequest()));

        $context
            ->expects($this->any())
            ->method('getUser')
            ->will($this->returnValue($user ? $user : $this->getSupportedUser()));

        return $context;
    }

    private function getRequest()
    {
        $request = $this->getMockBuilder("Symfony\Component\HttpFoundation\Request")
            ->disableOriginalConstructor()
            ->getMock();

        return $request;
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

    /**
     * @test
     * @dataProvider getTrustedOptionAndUsers
     */
    public function beginAuthentication_trustedOptionNotUsed_setUseTrustedOptionFalse($trustedOptionEnabled, $user)
    {
        $context = $this->getAuthenticationContext(null, $user);
        $trustedFilter = $this->getTrustedFilter($this->authHandler, $this->cookieManager, $trustedOptionEnabled);

        //Mock the context
        $context
            ->expects($this->once())
            ->method('setUseTrustedOption')
            ->with(false);

        $trustedFilter->beginAuthentication($context);
    }

    /**
     * Return test data for trustedOption and user object.
     *
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
    public function beginAuthentication_trustedOptionUsed_setUseTrustedOptionTrue()
    {
        $context = $this->getAuthenticationContext();

        //Mock the context
        $context
            ->expects($this->once())
            ->method('setUseTrustedOption')
            ->with(true);

        $this->trustedFilter->beginAuthentication($context);
    }

    /**
     * @test
     */
    public function beginAuthentication_trustedOptionUsed_checkTrustedCookie()
    {
        $request = $this->getRequest();
        $user = $this->getSupportedUser();
        $context = $this->getAuthenticationContext();

        //Mock the TrustedCookieManager
        $this->cookieManager
            ->expects($this->once())
            ->method('isTrustedComputer')
            ->with($request, $user);

        $this->trustedFilter->beginAuthentication($context);
    }

    /**
     * @test
     */
    public function beginAuthentication_isTrustedComputer_notCallAuthenticationHandler()
    {
        $context = $this->getAuthenticationContext();

        //Stub the TrustedCookieManager
        $this->cookieManager
            ->expects($this->any())
            ->method('isTrustedComputer')
            ->will($this->returnValue(true));

        //Mock the authentication handler
        $this->authHandler
            ->expects($this->never())
            ->method('beginAuthentication');

        $this->trustedFilter->beginAuthentication($context);
    }

    /**
     * @test
     */
    public function beginAuthentication_notTrustedComputer_callAuthenticationHandler()
    {
        $context = $this->getAuthenticationContext();

        //Stub the TrustedCookieManager
        $this->cookieManager
            ->expects($this->any())
            ->method('isTrustedComputer')
            ->will($this->returnValue(false));

        //Mock the authentication handler
        $this->authHandler
            ->expects($this->once())
            ->method('beginAuthentication')
            ->with($context);

        $this->trustedFilter->beginAuthentication($context);
    }

    /**
     * @test
     * @dataProvider getTrustedOptionAndUsers
     */
    public function requestAuthenticationCode_trustedOptionNotUsed_setUseTrustedOptionFalse($trustedOptionEnabled, $user)
    {
        $context = $this->getAuthenticationContext(null, $user);
        $trustedFilter = $this->getTrustedFilter($this->authHandler, $this->cookieManager, $trustedOptionEnabled);

        //Mock the context
        $context
            ->expects($this->once())
            ->method('setUseTrustedOption')
            ->with(false);

        $trustedFilter->requestAuthenticationCode($context);
    }

    /**
     * @test
     */
    public function requestAuthenticationCode_trustedOptionUsed_setUseTrustedOptionTrue()
    {
        $context = $this->getAuthenticationContext();

        //Mock the context
        $context
            ->expects($this->once())
            ->method('setUseTrustedOption')
            ->with(true);

        $this->trustedFilter->requestAuthenticationCode($context);
    }

    /**
     * @test
     * @dataProvider getResponseAndExpectedReturnValue
     */
    public function requestAuthenticationCode_createNoResponse_returnNull($response)
    {
        $context = $this->getAuthenticationContext();

        //Mock the authentication handler
        $this->authHandler
            ->expects($this->once())
            ->method('requestAuthenticationCode')
            ->with($context)
            ->will($this->returnValue($response));

        $returnValue = $this->trustedFilter->requestAuthenticationCode($context);
        $this->assertNull($returnValue);
    }

    /**
     * Return test data for response and expected return value.
     *
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
        $context = $this->getAuthenticationContext();

        //Mock the authentication handler
        $this->authHandler
            ->expects($this->once())
            ->method('requestAuthenticationCode')
            ->with($context)
            ->will($this->returnValue(new Response('<form></form>')));

        $returnValue = $this->trustedFilter->requestAuthenticationCode($context);
        $this->assertInstanceOf("Symfony\Component\HttpFoundation\Response", $returnValue);
        $this->assertEquals('<form></form>', $returnValue->getContent());
    }

    /**
     * @test
     */
    public function requestAuthenticationCode_notAuthenticated_notSetTrustedCookie()
    {
        $request = $this->getRequest();
        $request
            ->expects($this->any())
            ->method('get')
            ->with('trustedName')
            ->will($this->returnValue(true)); //Trusted option checked
        $context = $this->getAuthenticationContext($request);

        //Stub the authentication handler
        $this->authHandler
            ->expects($this->once())
            ->method('requestAuthenticationCode')
            ->will($this->returnValue(new Response('<form></form>')));

        //Mock the TrustedCookieManager
        $this->cookieManager
            ->expects($this->never())
            ->method('createTrustedCookie');

        $this->trustedFilter->requestAuthenticationCode($context);
    }

    /**
     * @test
     */
    public function requestAuthenticationCode_authenticatedTrustedNotChecked_notSetTrustedCookie()
    {
        $request = $this->getRequest();
        $request
            ->expects($this->any())
            ->method('get')
            ->with('trustedName')
            ->will($this->returnValue(false)); //Trusted option not checked

        //Stub the context
        $context = $this->getAuthenticationContext($request);
        $context
            ->expects($this->any())
            ->method('isAuthenticated')
            ->will($this->returnValue(true));

        //Stub the authentication handler
        $this->authHandler
            ->expects($this->once())
            ->method('requestAuthenticationCode')
            ->will($this->returnValue(new Response('<form></form>')));

        //Mock the TrustedCookieManager
        $this->cookieManager
            ->expects($this->never())
            ->method('createTrustedCookie');

        $this->trustedFilter->requestAuthenticationCode($context);
    }

    /**
     * @test
     */
    public function requestAuthenticationCode_authenticatedAndTrustedChecked_setTrustedCookie()
    {
        $request = $this->getRequest();
        $request
            ->expects($this->any())
            ->method('get')
            ->with('trustedName')
            ->will($this->returnValue(true)); //Trusted option checked
        $user = $this->getSupportedUser();

        //Stub the context
        $context = $this->getAuthenticationContext($request, $user);
        $context
            ->expects($this->any())
            ->method('isAuthenticated')
            ->will($this->returnValue(true));

        //Stub the authentication handler
        $response = $this->getResponse();
        $this->authHandler
            ->expects($this->once())
            ->method('requestAuthenticationCode')
            ->will($this->returnValue($response));

        //Mock the TrustedCookieManager
        $cookie = new Cookie('someCookie');
        $this->cookieManager
            ->expects($this->once())
            ->method('createTrustedCookie')
            ->with($request, $user)
            ->will($this->returnValue($cookie));

        //Expect cookie be set in response headers
        $response->headers
            ->expects($this->once())
            ->method('setCookie')
            ->with($cookie);

        $this->trustedFilter->requestAuthenticationCode($context);
    }
}
