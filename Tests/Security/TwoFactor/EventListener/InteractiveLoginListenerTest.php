<?php

namespace Scheb\TwoFactorBundle\Tests\Security\TwoFactor\EventListener;

use Scheb\TwoFactorBundle\Security\TwoFactor\EventListener\InteractiveLoginListener;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Scheb\TwoFactorBundle\Security\TwoFactor\AuthenticationContext;
use Scheb\TwoFactorBundle\Tests\TestCase;

class InteractiveLoginListenerTest extends TestCase
{
    const WHITELISTED_IP = '1.2.3.4';
    const NON_WHITELISTED_IP = '1.1.1.1';

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
    private $request;

    /**
     * @var InteractiveLoginListener
     */
    private $listener;

    public function setUp()
    {
        $this->authenticationContextFactory = $this->createMock('Scheb\TwoFactorBundle\Security\TwoFactor\AuthenticationContextFactoryInterface');
        $this->authHandler = $this->createMock('Scheb\TwoFactorBundle\Security\TwoFactor\AuthenticationHandlerInterface');

        $supportedTokens = array('Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken');
        $this->listener = new InteractiveLoginListener($this->authenticationContextFactory, $this->authHandler, $supportedTokens, array(self::WHITELISTED_IP));
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
            ->willReturn($clientIp);

        $event = $this->createMock('Symfony\Component\Security\Http\Event\InteractiveLoginEvent');
        $event
            ->expects($this->any())
            ->method('getRequest')
            ->willReturn($this->request);
        $event
            ->expects($this->any())
            ->method('getAuthenticationToken')
            ->willReturn($token);

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

        $this->authenticationContextFactory
            ->expects($this->once())
            ->method('create')
            ->willReturn($expectedContext);

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

    /**
     * @test
     */
    public function onSecurityInteractiveLogin_NotLoggedInUser_notRequestAuthenticationCode()
    {
        // simulate a not logged in user
        $event = $this->createEvent(null, self::NON_WHITELISTED_IP);

        //Expect TwoFactorProvider not to be called
        $this->authHandler
            ->expects($this->never())
            ->method('beginAuthentication');

        $this->listener->onSecurityInteractiveLogin($event);
    }
}
