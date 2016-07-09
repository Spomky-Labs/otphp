<?php

namespace Scheb\TwoFactorBundle\Tests\Security\TwoFactor\EventListener;

use Scheb\TwoFactorBundle\Security\TwoFactor\EventListener\InteractiveLoginListener;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Scheb\TwoFactorBundle\Security\TwoFactor\AuthenticationContext;

class InteractiveLoginListenerTest extends \PHPUnit_Framework_TestCase
{
    const WHITELISTED_IP = '1.2.3.4';
    const NON_WHITELISTED_IP = '1.1.1.1';

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $authHandler;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $request;

    /**
     * @var InteractiveLoginListener
     */
    private $listener;

    public function setUp()
    {
        $this->authHandler = $this->createMock('Scheb\TwoFactorBundle\Security\TwoFactor\AuthenticationHandlerInterface');

        $supportedTokens = array('Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken');
        $this->listener = new InteractiveLoginListener($this->authHandler, $supportedTokens, array(self::WHITELISTED_IP));
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function createEvent($token, $clientIp)
    {
        $this->request = $this->createMock('Symfony\Component\HttpFoundation\Request');
        $this->request
            ->expects($this->any())
            ->method('getClientIp')
            ->will($this->returnValue($clientIp));

        $event = $this->getMockBuilder('Symfony\Component\Security\Http\Event\InteractiveLoginEvent')
            ->disableOriginalConstructor()
            ->getMock();
        $event
            ->expects($this->any())
            ->method('getRequest')
            ->will($this->returnValue($this->request));
        $event
            ->expects($this->any())
            ->method('getAuthenticationToken')
            ->will($this->returnValue($token));

        return $event;
    }

    /**
     * @test
     */
    public function onSecurityInteractiveLogin_tokenClassSupported_beginAuthentication()
    {
        $token = new UsernamePasswordToken('user', array(), 'key');
        $event = $this->createEvent($token, self::NON_WHITELISTED_IP);

        //Expect TwoFactorProvider to be called
        $expectedContext = new AuthenticationContext($this->request, $token);
        $this->authHandler
            ->expects($this->once())
            ->method('beginAuthentication')
            ->with($expectedContext);

        $this->listener->onSecurityInteractiveLogin($event);
    }

    /**
     * @test
     */
    public function onSecurityInteractiveLogin_tokenClassNotSupported_doNothing()
    {
        $token = $this->createMock('Symfony\Component\Security\Core\Authentication\Token\TokenInterface');
        $event = $this->createEvent($token, self::NON_WHITELISTED_IP);

        //Expect TwoFactorProvider not to be called
        $this->authHandler
            ->expects($this->never())
            ->method('beginAuthentication');

        $this->listener->onSecurityInteractiveLogin($event);
    }

    /**
     * @test
     */
    public function onSecurityInteractiveLogin_ipWhitelisted_doNothing()
    {
        $token = $this->createMock('Symfony\Component\Security\Core\Authentication\Token\TokenInterface');
        $event = $this->createEvent($token, self::WHITELISTED_IP);

        //Expect TwoFactorProvider not to be called
        $this->authHandler
            ->expects($this->never())
            ->method('beginAuthentication');

        $this->listener->onSecurityInteractiveLogin($event);
    }
}
