<?php

namespace Scheb\TwoFactorBundle\Tests\Security\TwoFactor\EventListener;

use Scheb\TwoFactorBundle\Security\TwoFactor\EventListener\RequestListener;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Scheb\TwoFactorBundle\Security\TwoFactor\AuthenticationContext;

class RequestListenerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $authHandler;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $tokenStorage;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $request;

    /**
     * @var RequestListener
     */
    private $listener;

    public function setUp()
    {
        $this->authHandler = $this->createMock('Scheb\TwoFactorBundle\Security\TwoFactor\AuthenticationHandlerInterface');
        $this->tokenStorage = $this->createMock('Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface');

        $supportedTokens = array('Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken');
        $this->listener = new RequestListener($this->authHandler, $this->tokenStorage, $supportedTokens, '^/exclude/');
    }

    /**
     * @return UsernamePasswordToken
     */
    private function createSupportedSecurityToken()
    {
        return new UsernamePasswordToken('user', array(), 'key');
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function createEvent($pathInfo = '/some-path/')
    {
        $this->request = $this->createMock('Symfony\Component\HttpFoundation\Request');
        $this->request
            ->expects($this->any())
            ->method('getPathInfo')
            ->will($this->returnValue($pathInfo));

        $event = $this->getMockBuilder('Symfony\Component\HttpKernel\Event\GetResponseEvent')
            ->disableOriginalConstructor()
            ->getMock();
        $event
            ->expects($this->any())
            ->method('getRequest')
            ->will($this->returnValue($this->request));

        return $event;
    }

    private function stubTokenStorage($token)
    {
        $this->tokenStorage
            ->expects($this->any())
            ->method('getToken')
            ->will($this->returnValue($token));
    }

    /**
     * @test
     */
    public function onCoreRequest_tokenClassSupported_requestAuthenticationCode()
    {
        $event = $this->createEvent();
        $token = $this->createSupportedSecurityToken();
        $this->stubTokenStorage($token);

        //Expect TwoFactorProvider to be called
        $expectedContext = new AuthenticationContext($this->request, $token);
        $this->authHandler
            ->expects($this->once())
            ->method('requestAuthenticationCode')
            ->with($expectedContext);

        $this->listener->onCoreRequest($event);
    }

    /**
     * @test
     */
    public function onCoreRequest_responseCreated_setResponseOnEvent()
    {
        $event = $this->createEvent();
        $token = $this->createSupportedSecurityToken();
        $this->stubTokenStorage($token);
        $response = $this->createMock('Symfony\Component\HttpFoundation\Response');

        //Stub the TwoFactorProvider
        $this->authHandler
            ->expects($this->any())
            ->method('requestAuthenticationCode')
            ->will($this->returnValue($response));

        //Expect response to be set
        $event
            ->expects($this->once())
            ->method('setResponse')
            ->with($response);

        $this->listener->onCoreRequest($event);
    }

    /**
     * @test
     */
    public function onCoreRequest_tokenClassNotSupported_doNothing()
    {
        $event = $this->createEvent();
        $token = $this->createMock('Symfony\Component\Security\Core\Authentication\Token\TokenInterface');
        $this->stubTokenStorage($token);

        //Stub the TwoFactorProvider
        $this->authHandler
            ->expects($this->never())
            ->method('requestAuthenticationCode');

        $this->listener->onCoreRequest($event);
    }

    /**
     * @test
     */
    public function onCoreRequest_pathExcluded_notRequestAuthenticationCode()
    {
        $event = $this->createEvent('/exclude/someFile');
        $token = $this->createSupportedSecurityToken();
        $this->stubTokenStorage($token);

        //Expect TwoFactorProvider to be called
        $this->authHandler
            ->expects($this->never())
            ->method('requestAuthenticationCode');

        $this->listener->onCoreRequest($event);
    }
}
