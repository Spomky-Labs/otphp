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
    private $authenticationContextFactory;

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
        $this->authenticationContextFactory = $this->getMock('Scheb\TwoFactorBundle\Security\TwoFactor\AuthenticationContextFactoryInterface');
        $this->authHandler = $this->getMock('Scheb\TwoFactorBundle\Security\TwoFactor\AuthenticationHandlerInterface');
        $this->tokenStorage = $this->getMock('Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface');

        $supportedTokens = array('Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken');
        $this->listener = new RequestListener($this->authenticationContextFactory, $this->authHandler, $this->tokenStorage, $supportedTokens, '^/exclude/');
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
        $this->request = $this->getMock('Symfony\Component\HttpFoundation\Request');
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

        $expectedContext = new AuthenticationContext($this->request, $token);

        $this->authenticationContextFactory
            ->expects($this->once())
            ->method('create')
            ->will($this->returnValue($expectedContext));

        //Expect TwoFactorProvider to be called
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
        $response = $this->getMock('Symfony\Component\HttpFoundation\Response');

        $expectedContext = new AuthenticationContext($this->request, $token);

        $this->authenticationContextFactory
            ->method('create')
            ->will($this->returnValue($expectedContext))
        ;

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
        $token = $this->getMock('Symfony\Component\Security\Core\Authentication\Token\TokenInterface');
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
