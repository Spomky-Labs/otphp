<?php

namespace Scheb\TwoFactorBundle\Tests\Security\TwoFactor\Provider;

use PHPUnit\Framework\MockObject\MockObject;
use Scheb\TwoFactorBundle\Security\TwoFactor\AuthenticationContextInterface;
use Scheb\TwoFactorBundle\Security\TwoFactor\Event\TwoFactorAuthenticationEvent;
use Scheb\TwoFactorBundle\Security\TwoFactor\Event\TwoFactorAuthenticationEvents;
use Scheb\TwoFactorBundle\Security\TwoFactor\Provider\TwoFactorProviderInterface;
use Scheb\TwoFactorBundle\Security\TwoFactor\Provider\TwoFactorProviderRegistry;
use Scheb\TwoFactorBundle\Security\TwoFactor\Session\SessionFlagManager;
use Scheb\TwoFactorBundle\Tests\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class TwoFactorProviderRegistryTest extends TestCase
{
    /**
     * @var MockObject|EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * @var MockObject|SessionFlagManager
     */
    private $flagManager;

    /**
     * @var MockObject|TwoFactorProviderInterface
     */
    private $provider;

    /**
     * @var TwoFactorProviderRegistry
     */
    private $registry;

    public function setUp()
    {
        $this->eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $this->flagManager = $this->createMock(SessionFlagManager::class);
        $this->provider = $this->createMock(TwoFactorProviderInterface::class);
        $this->registry = new TwoFactorProviderRegistry($this->flagManager, $this->eventDispatcher, '_auth_code', ['test' => $this->provider]);
    }

    private function getToken()
    {
        $token = $this->createMock(TokenInterface::class);

        return $token;
    }

    private function getAuthenticationContext($token = null, $authenticated = false, $authCode = null)
    {
        $context = $this->createMock(AuthenticationContextInterface::class);
        $context
            ->expects($this->any())
            ->method('getToken')
            ->willReturn($token ? $token : $this->getToken());

        $context
            ->expects($this->any())
            ->method('isAuthenticated')
            ->willReturn($authenticated);

        $request = $this->createMock(Request::class);
        $request
            ->expects($this->any())
            ->method('get')
            ->with('_auth_code')
            ->willReturn($authCode);

        $context
            ->expects($this->any())
            ->method('getRequest')
            ->will($this->returnValue($request));


        return $context;
    }

    private function stubIsNotAuthenticated($notAuthenticated)
    {
        $this->flagManager
            ->expects($this->any())
            ->method('isNotAuthenticated')
            ->willReturn($notAuthenticated);
    }

    private function assertDispatchAuthenticationEvent($eventType)
    {
        $this->eventDispatcher
            ->expects($this->once())
            ->method('dispatch')
            ->with(
                $this->equalTo($eventType),
                $this->isInstanceOf(TwoFactorAuthenticationEvent::class)
            );
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
            ->willReturn(true);

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
            ->willReturn(false);

        //Mock the SessionFlagManager
        $this->flagManager
            ->expects($this->once())
            ->method('setBegin');

        $this->flagManager
            ->expects($this->once())
            ->method('setAborted');

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
    public function requestAuthenticationCode_alreadyAuthenticated_notCallTwoFactorProvider()
    {
        $context = $this->getAuthenticationContext();

        //Stub the SessionFlagManager
        $this->stubIsNotAuthenticated(false);

        //Mock the provider
        $this->provider
            ->expects($this->never())
            ->method('requestAuthenticationCode');

        // Must not dispatch event
        $this->eventDispatcher
            ->expects($this->never())
            ->method('dispatch');

        $this->registry->requestAuthenticationCode($context);
    }

    /**
     * @test
     */
    public function requestAuthenticationCode_notAuthenticated_callTwoFactorProvider()
    {
        $context = $this->getAuthenticationContext();

        //Stub the SessionFlagManager
        $this->stubIsNotAuthenticated(true);

        //Mock the provider
        $this->provider
            ->expects($this->once())
            ->method('requestAuthenticationCode')
            ->with($context);

        // Must not dispatch event
        $this->eventDispatcher
            ->expects($this->never())
            ->method('dispatch');

        $this->registry->requestAuthenticationCode($context);
    }

    /**
     * @test
     */
    public function requestAuthenticationCode_authenticationSuccessful_updateSessionFlag()
    {
        $token = $this->getToken();
        $context = $this->getAuthenticationContext($token, true, 'authCodeValue');

        //Stub the SessionFlagManager
        $this->stubIsNotAuthenticated(true);

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
    public function requestAuthenticationCode_authenticationSuccessful_dispatchEvent()
    {
        $token = $this->getToken();
        $context = $this->getAuthenticationContext($token, true, 'authCodeValue');

        //Stub the SessionFlagManager
        $this->stubIsNotAuthenticated(true);

        // Dispatch authentication success event
        $this->assertDispatchAuthenticationEvent(TwoFactorAuthenticationEvents::SUCCESS);

        $this->registry->requestAuthenticationCode($context);
    }

    /**
     * @test
     */
    public function requestAuthenticationCode_authenticationFailed_dispatchEvent()
    {
        $token = $this->getToken();
        $context = $this->getAuthenticationContext($token, false, 'authCodeValue');

        //Stub the SessionFlagManager
        $this->stubIsNotAuthenticated(true);

        // Dispatch authentication success event
        $this->assertDispatchAuthenticationEvent(TwoFactorAuthenticationEvents::FAILURE);

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
        $this->stubIsNotAuthenticated(true);

        //Stub the provider
        $this->provider
            ->expects($this->any())
            ->method('requestAuthenticationCode')
            ->willReturn(new Response('<form></form>'));

        $returnValue = $this->registry->requestAuthenticationCode($context);
        $this->assertInstanceOf(Response::class, $returnValue);
        $this->assertEquals('<form></form>', $returnValue->getContent());
    }
}
