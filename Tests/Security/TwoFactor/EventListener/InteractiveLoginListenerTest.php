<?php
namespace Scheb\TwoFactorBundle\Tests\Security\TwoFactor\EventListener;

use Scheb\TwoFactorBundle\Security\TwoFactor\EventListener\InteractiveLoginListener;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

class InteractiveLoginListenerTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $provider;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $request;

    /**
     * @var \Scheb\TwoFactorBundle\Security\TwoFactor\EventListener\InteractiveLoginListener
     */
    private $listener;

    public function setUp()
    {
        $this->provider = $this->getMockBuilder("Scheb\TwoFactorBundle\Security\TwoFactor\Provider\TwoFactorProviderRegistry")
            ->disableOriginalConstructor()
            ->getMock();

        $supportedTokens = array("Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken");
        $this->listener = new InteractiveLoginListener($this->provider, $supportedTokens);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function createEvent($token)
    {
        $this->request = $this->getMock("Symfony\Component\HttpFoundation\Request");
        $event = $this->getMockBuilder("Symfony\Component\Security\Http\Event\InteractiveLoginEvent")
            ->disableOriginalConstructor()
            ->getMock();
        $event
            ->expects($this->any())
            ->method("getRequest")
            ->will($this->returnValue($this->request));
        $event
            ->expects($this->any())
            ->method("getAuthenticationToken")
            ->will($this->returnValue($token));

        return $event;
    }

    /**
     * @test
     */
    public function onSecurityInteractiveLogin_tokenClassSupported_beginAuthentication()
    {
        $token = new UsernamePasswordToken("user", array(), "key");
        $event = $this->createEvent($token);

        //Expect TwoFactorProvider to be called
        $this->provider
            ->expects($this->once())
            ->method("beginAuthentication")
            ->with($this->request, $token);

        $this->listener->onSecurityInteractiveLogin($event);
    }

    /**
     * @test
     */
    public function onSecurityInteractiveLogin_tokenClassNotSupported_doNothing()
    {
        $token = $this->getMock("Symfony\Component\Security\Core\Authentication\Token\TokenInterface");
        $event = $this->createEvent($token);

        //Expect TwoFactorProvider not to be called
        $this->provider
            ->expects($this->never())
            ->method("beginAuthentication");

        $this->listener->onSecurityInteractiveLogin($event);
    }

}
