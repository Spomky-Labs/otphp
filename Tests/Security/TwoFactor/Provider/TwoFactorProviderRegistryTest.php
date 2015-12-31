<?php

namespace Scheb\TwoFactorBundle\Tests\Security\TwoFactor\Provider;

use Scheb\TwoFactorBundle\Security\TwoFactor\Provider\TwoFactorProviderRegistry;
use Symfony\Component\HttpFoundation\Response;

class TwoFactorProviderRegistryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $flagManager;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $provider;

    /**
     * @var TwoFactorProviderRegistry
     */
    private $registry;

    public function setUp()
    {
        $this->flagManager = $this->getMockBuilder("Scheb\TwoFactorBundle\Security\TwoFactor\Session\SessionFlagManager")
            ->disableOriginalConstructor()
            ->getMock();

        $this->provider = $this->getMock("Scheb\TwoFactorBundle\Security\TwoFactor\Provider\TwoFactorProviderInterface");

        $this->registry = new TwoFactorProviderRegistry($this->flagManager, array('test' => $this->provider));
    }

    private function getToken()
    {
        $token = $this->getMock("Symfony\Component\Security\Core\Authentication\Token\TokenInterface");

        return $token;
    }

    private function getAuthenticationContext($token = null, $authenticated = false)
    {
        $context = $this->getMockBuilder("Scheb\TwoFactorBundle\Security\TwoFactor\AuthenticationContext")
            ->disableOriginalConstructor()
            ->getMock();

        $context
            ->expects($this->any())
            ->method('getToken')
            ->will($this->returnValue($token ? $token : $this->getToken()));

        $context
            ->expects($this->any())
            ->method('isAuthenticated')
            ->will($this->returnValue($authenticated));

        return $context;
    }

    /**
     * @test
     */
    public function beginAuthentication_onCall_callTwoFactorProvider()
    {
        $context = $this->getAuthenticationContext();

        //Mock the provider
        $this->provider
            ->expects($this->any())
            ->method('beginAuthentication')
            ->with($context);

        $this->registry->beginAuthentication($context);
    }

    /**
     * @test
     */
    public function beginAuthentication_authenticationStarted_sessionFlagSet()
    {
        $token = $this->getToken();
        $context = $this->getAuthenticationContext($token);

        //Stub the provider
        $this->provider
            ->expects($this->any())
            ->method('beginAuthentication')
            ->will($this->returnValue(true));

        //Mock the SessionFlagManager
        $this->flagManager
            ->expects($this->once())
            ->method('setBegin')
            ->with('test', $token);

        $this->registry->beginAuthentication($context);
    }

    /**
     * @test
     */
    public function beginAuthentication_authenticationNotStarted_notSetSessionFlag()
    {
        $token = $this->getToken();
        $context = $this->getAuthenticationContext($token);

        //Stub the provider
        $this->provider
            ->expects($this->any())
            ->method('beginAuthentication')
            ->will($this->returnValue(false));

        //Mock the SessionFlagManager
        $this->flagManager
            ->expects($this->never())
            ->method('setBegin');

        $this->registry->beginAuthentication($context);
    }

    /**
     * @test
     */
    public function requestAuthenticationCode_onCall_checkIfAuthenticationComplete()
    {
        $token = $this->getToken();
        $context = $this->getAuthenticationContext($token);

        //Mock the SessionFlagManager
        $this->flagManager
            ->expects($this->once())
            ->method('isNotAuthenticated')
            ->with('test', $token);

        $this->registry->requestAuthenticationCode($context);
    }

    /**
     * @test
     */
    public function requestAuthenticationCode_notAuthenticated_callTwoFactorProvider()
    {
        $context = $this->getAuthenticationContext();

        //Stub the SessionFlagManager
        $this->flagManager
            ->expects($this->any())
            ->method('isNotAuthenticated')
            ->will($this->returnValue(true));

        //Mock the provider
        $this->provider
            ->expects($this->once())
            ->method('requestAuthenticationCode')
            ->with($context);

        $this->registry->requestAuthenticationCode($context);
    }

    /**
     * @test
     */
    public function requestAuthenticationCode_alreadyAuthenticated_notCallTwoFactorProvider()
    {
        $context = $this->getAuthenticationContext();

        //Stub the SessionFlagManager
        $this->flagManager
            ->expects($this->any())
            ->method('isNotAuthenticated')
            ->will($this->returnValue(false));

        //Mock the provider
        $this->provider
            ->expects($this->never())
            ->method('requestAuthenticationCode');

        $this->registry->requestAuthenticationCode($context);
    }

    /**
     * @test
     */
    public function requestAuthenticationCode_authenticationIsCompleted_updateSessionFlag()
    {
        $token = $this->getToken();
        $context = $this->getAuthenticationContext($token, true);

        //Stub the SessionFlagManager
        $this->flagManager
            ->expects($this->any())
            ->method('isNotAuthenticated')
            ->will($this->returnValue(true));

        //Expect flag to be set
        $this->flagManager
            ->expects($this->once())
            ->method('setComplete')
            ->with('test', $token);

        $this->registry->requestAuthenticationCode($context);
    }

    /**
     * @test
     */
    public function requestAuthenticationCode_requestAuthenticationCode_returnResponse()
    {
        $token = $this->getToken();
        $context = $this->getAuthenticationContext($token, true);

        //Stub the SessionFlagManager
        $this->flagManager
            ->expects($this->any())
            ->method('isNotAuthenticated')
            ->will($this->returnValue(true));

        //Stub the provider
        $this->provider
            ->expects($this->any())
            ->method('requestAuthenticationCode')
            ->will($this->returnValue(new Response('<form></form>')));

        $returnValue = $this->registry->requestAuthenticationCode($context);
        $this->assertInstanceOf("Symfony\Component\HttpFoundation\Response", $returnValue);
        $this->assertEquals('<form></form>', $returnValue->getContent());
    }
}
