<?php

namespace Scheb\TwoFactorBundle\Tests\Security\Http\Firewall;

use PHPUnit\Framework\MockObject\MockObject;
use Psr\Log\LoggerInterface;
use Scheb\TwoFactorBundle\Security\Authentication\Token\TwoFactorToken;
use Scheb\TwoFactorBundle\Security\Authentication\Voter\TwoFactorInProgressVoter;
use Scheb\TwoFactorBundle\Security\Http\Firewall\TwoFactorListener;
use Scheb\TwoFactorBundle\Security\TwoFactor\Event\TwoFactorAuthenticationEvent;
use Scheb\TwoFactorBundle\Security\TwoFactor\Event\TwoFactorAuthenticationEvents;
use Scheb\TwoFactorBundle\Security\TwoFactor\Trusted\TrustedDeviceManagerInterface;
use Scheb\TwoFactorBundle\Tests\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\Security\Core\Authentication\AuthenticationManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\AccessMapInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationFailureHandlerInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;
use Symfony\Component\Security\Http\HttpUtils;

class TwoFactorListenerTest extends TestCase
{
    const FORM_PATH = '/form_path';
    const CHECK_PATH = '/check_path';
    const AUTH_CODE_PARAM = 'auth_code_param';
    const TRUSTED_PARAM = 'trusted_param';
    const FIREWALL_NAME = 'firewallName';

    /**
     * @var MockObject|TokenStorageInterface
     */
    private $tokenStorage;

    /**
     * @var MockObject|AuthenticationManagerInterface
     */
    private $authenticationManager;

    /**
     * @var MockObject|HttpUtils
     */
    private $httpUtils;

    /**
     * @var MockObject|AuthenticationSuccessHandlerInterface
     */
    private $successHandler;

    /**
     * @var MockObject|AuthenticationFailureHandlerInterface
     */
    private $failureHandler;

    /**
     * @var MockObject|TrustedDeviceManagerInterface
     */
    private $trustedDeviceManager;

    /**
     * @var MockObject|EventDispatcherInterface
     */
    private $dispatcher;

    /**
     * @var MockObject|AccessMapInterface
     */
    private $accessMap;

    /**
     * @var MockObject|AccessDecisionManagerInterface
     */
    private $accessDecisionManager;

    /**
     * @var MockObject|GetResponseEvent
     */
    private $getResponseEvent;

    /**
     * @var MockObject|Request
     */
    private $request;

    /**
     * @var array
     */
    private $requestParams = [
        self::AUTH_CODE_PARAM => 'authCode',
        self::TRUSTED_PARAM => null,
    ];

    /**
     * @var MockObject|RedirectResponse
     */
    private $authFormRedirectResponse;

    /**
     * @var TwoFactorListener
     */
    private $listener;

    protected function setUp()
    {
        $this->tokenStorage = $this->createMock(TokenStorageInterface::class);
        $this->authenticationManager = $this->createMock(AuthenticationManagerInterface::class);
        $this->httpUtils = $this->createMock(HttpUtils::class);
        $this->successHandler = $this->createMock(AuthenticationSuccessHandlerInterface::class);
        $this->failureHandler = $this->createMock(AuthenticationFailureHandlerInterface::class);
        $this->trustedDeviceManager = $this->createMock(TrustedDeviceManagerInterface::class);
        $this->accessMap = $this->createMock(AccessMapInterface::class);
        $this->accessDecisionManager = $this->createMock(AccessDecisionManagerInterface::class);
        $this->dispatcher = $this->createMock(EventDispatcherInterface::class);

        $this->request = $this->createMock(Request::class);
        $this->request
            ->expects($this->any())
            ->method('get')
            ->willReturnCallback(function (string $param) {
                return $this->requestParams[$param];
            });

        $this->getResponseEvent = $this->createMock(GetResponseEvent::class);
        $this->getResponseEvent
            ->expects($this->any())
            ->method('getRequest')
            ->willReturn($this->request);

        $this->authFormRedirectResponse = $this->createMock(RedirectResponse::class);
        $this->httpUtils
            ->expects($this->any())
            ->method('createRedirectResponse')
            ->with($this->request, self::FORM_PATH)
            ->willReturn($this->authFormRedirectResponse);

        $options = [
            'auth_form_path' => self::FORM_PATH,
            'check_path' => self::CHECK_PATH,
            'auth_code_parameter_name' => self::AUTH_CODE_PARAM,
            'trusted_parameter_name' => self::TRUSTED_PARAM,
        ];

        $this->listener = new TwoFactorListener(
            $this->tokenStorage,
            $this->authenticationManager,
            $this->httpUtils,
            self::FIREWALL_NAME,
            $this->successHandler,
            $this->failureHandler,
            $options,
            $this->trustedDeviceManager,
            $this->accessMap,
            $this->accessDecisionManager,
            $this->dispatcher,
            $this->createMock(LoggerInterface::class)
        );
    }

    private function createTwoFactorToken($firewallName = self::FIREWALL_NAME): MockObject
    {
        $twoFactorToken = $this->createMock(TwoFactorToken::class);
        $twoFactorToken
            ->expects($this->any())
            ->method('getProviderKey')
            ->willReturn($firewallName);

        return $twoFactorToken;
    }

    private function stubTokenManagerHasToken(TokenInterface $token): void
    {
        $this->tokenStorage
            ->expects($this->any())
            ->method('getToken')
            ->willReturn($token);
    }

    private function stubCurrentPath(string $currentPath): void
    {
        $this->request
            ->expects($this->any())
            ->method('getUri')
            ->willReturn($currentPath);

        $this->httpUtils
            ->expects($this->any())
            ->method('checkRequestPath')
            ->with($this->request)
            ->willReturnCallback(function ($request, $pathToCheck) use ($currentPath) {
                return $currentPath === $pathToCheck;
            });
    }

    private function stubRequestHasParameter(string $parameterName, $value): void
    {
        $this->requestParams[$parameterName] = $value;
    }

    private function stubHandlersReturnResponse(): void
    {
        $this->successHandler
            ->expects($this->any())
            ->method('onAuthenticationSuccess')
            ->willReturn($this->createMock(Response::class));
        $this->failureHandler
            ->expects($this->any())
            ->method('onAuthenticationFailure')
            ->willReturn($this->createMock(Response::class));
    }

    private function stubAuthenticationManagerReturnsToken(MockObject $returnedToken): void
    {
        $this->authenticationManager
            ->expects($this->any())
            ->method('authenticate')
            ->willReturn($returnedToken);
    }

    private function stubAuthenticationManagerThrowsAuthenticationException(): void
    {
        $this->authenticationManager
            ->expects($this->any())
            ->method('authenticate')
            ->willThrowException(new AuthenticationException());
    }

    private function stubPathAccessGranted(bool $accessGranted): void
    {
        $this->accessMap
            ->expects($this->any())
            ->method('getPatterns')
            ->willReturn([[TwoFactorInProgressVoter::IS_AUTHENTICATED_2FA_IN_PROGRESS], 'https']);
        $this->accessDecisionManager
            ->expects($this->any())
            ->method('decide')
            ->with($this->isInstanceOf(TwoFactorToken::class), [TwoFactorInProgressVoter::IS_AUTHENTICATED_2FA_IN_PROGRESS], $this->request)
            ->willReturn($accessGranted);
    }

    private function assertPathNotChecked(): void
    {
        $this->httpUtils
            ->expects($this->never())
            ->method($this->anything());
    }

    private function assertNoResponseSet(): void
    {
        $this->getResponseEvent
            ->expects($this->never())
            ->method('getResponse');
    }

    private function assertRedirectToAuthForm(): void
    {
        $this->getResponseEvent
            ->expects($this->once())
            ->method('setResponse')
            ->with($this->identicalTo($this->authFormRedirectResponse));
    }

    private function assertSaveTargetUrl(string $targetUrl): void
    {
        $session = $this->createMock(SessionInterface::class);
        $session
            ->expects($this->once())
            ->method('set')
            ->with('_security.firewallName.target_path', $targetUrl);

        $this->request
            ->expects($this->any())
            ->method('getSession')
            ->willReturn($session);

        // Conditions to store target URL
        $this->request
            ->expects($this->any())
            ->method('hasSession')
            ->willReturn(true);
        $this->request
            ->expects($this->any())
            ->method('isMethodSafe')
            ->willReturn(true);
        $this->request
            ->expects($this->any())
            ->method('isXmlHttpRequest')
            ->willReturn(false);
    }

    private function assertEventsDispatched(array $eventTypes): void
    {
        $numEvents = count($eventTypes);
        $consecutiveParams = [];
        foreach ($eventTypes as $eventType) {
            $consecutiveParams[] = [$eventType, $this->isInstanceOf(TwoFactorAuthenticationEvent::class)];
        }
        $this->dispatcher
            ->expects($this->exactly($numEvents))
            ->method('dispatch')
            ->withConsecutive(...$consecutiveParams);
    }

    /**
     * @test
     */
    public function handle_noTwoFactorToken_doNothing()
    {
        $this->stubTokenManagerHasToken($this->createMock(TokenInterface::class));

        $this->assertPathNotChecked();
        $this->assertNoResponseSet();

        $this->listener->handle($this->getResponseEvent);
    }

    /**
     * @test
     */
    public function handle_differentFirewallName_doNothing()
    {
        $this->stubTokenManagerHasToken($this->createTwoFactorToken('otherFirewallName'));

        $this->assertPathNotChecked();
        $this->assertNoResponseSet();

        $this->listener->handle($this->getResponseEvent);
    }

    /**
     * @test
     */
    public function handle_neitherFormNorCheckPath_redirectToFormAndSaveTargetPath()
    {
        $this->stubTokenManagerHasToken($this->createTwoFactorToken());
        $this->stubCurrentPath('/some_other_path');
        $this->stubPathAccessGranted(false);

        $this->assertRedirectToAuthForm();
        $this->assertSaveTargetUrl('/some_other_path');

        $this->listener->handle($this->getResponseEvent);
    }

    /**
     * @test
     */
    public function handle_pathAccessibleDuringTwoFactorAuthentication_notRedirectToForm()
    {
        $this->stubTokenManagerHasToken($this->createTwoFactorToken());
        $this->stubCurrentPath('/some_other_path');
        $this->stubPathAccessGranted(true);

        $this->assertNoResponseSet();

        $this->listener->handle($this->getResponseEvent);
    }

    /**
     * @test
     */
    public function handle_isAuthFormPath_doNothing()
    {
        $this->stubTokenManagerHasToken($this->createTwoFactorToken());
        $this->stubCurrentPath(self::FORM_PATH);

        $this->assertNoResponseSet();

        $this->listener->handle($this->getResponseEvent);
    }

    /**
     * @test
     */
    public function handle_isCheckPath_authenticateWithAuthenticationManager()
    {
        $this->stubTokenManagerHasToken($this->createTwoFactorToken());
        $this->stubCurrentPath(self::CHECK_PATH);
        $this->stubHandlersReturnResponse();

        $tokenAssert = function ($token): bool {
            /* @var TwoFactorToken $token */
            $this->assertInstanceOf(TwoFactorToken::class, $token);
            $this->assertEquals('authCode', $token->getCredentials());

            return true;
        };

        $this->authenticationManager
            ->expects($this->once())
            ->method('authenticate')
            ->with($this->callback($tokenAssert))
            ->willReturn($this->createMock(TokenInterface::class));

        $this->listener->handle($this->getResponseEvent);
    }

    /**
     * @test
     */
    public function handle_authenticationException_dispatchFailureEvent()
    {
        $this->stubTokenManagerHasToken($this->createTwoFactorToken());
        $this->stubCurrentPath(self::CHECK_PATH);
        $this->stubAuthenticationManagerThrowsAuthenticationException();
        $this->stubHandlersReturnResponse();

        $this->assertEventsDispatched([
            TwoFactorAuthenticationEvents::ATTEMPT,
            TwoFactorAuthenticationEvents::FAILURE,
        ]);

        $this->listener->handle($this->getResponseEvent);
    }

    /**
     * @test
     */
    public function handle_authenticationException_setResponseFromFailureHandler()
    {
        $this->stubTokenManagerHasToken($this->createTwoFactorToken());
        $this->stubCurrentPath(self::CHECK_PATH);
        $this->stubAuthenticationManagerThrowsAuthenticationException();

        $response = $this->createMock(Response::class);
        $this->failureHandler
            ->expects($this->once())
            ->method('onAuthenticationFailure')
            ->willReturn($response);

        $this->getResponseEvent
            ->expects($this->once())
            ->method('setResponse')
            ->with($this->identicalTo($response));

        $this->listener->handle($this->getResponseEvent);
    }

    /**
     * @test
     */
    public function handle_authenticationStepSuccessful_dispatchSuccessEvent()
    {
        $twoFactorToken = $this->createTwoFactorToken();
        $this->stubTokenManagerHasToken($twoFactorToken);
        $this->stubCurrentPath(self::CHECK_PATH);
        $this->stubAuthenticationManagerReturnsToken($twoFactorToken); // Must be TwoFactorToken

        $this->assertEventsDispatched([
            TwoFactorAuthenticationEvents::ATTEMPT,
            TwoFactorAuthenticationEvents::SUCCESS,
        ]);

        $this->listener->handle($this->getResponseEvent);
    }

    /**
     * @test
     */
    public function handle_authenticationStepSuccessful_redirectToAuthenticationForm()
    {
        $twoFactorToken = $this->createTwoFactorToken();
        $this->stubTokenManagerHasToken($twoFactorToken);
        $this->stubCurrentPath(self::CHECK_PATH);
        $this->stubAuthenticationManagerReturnsToken($twoFactorToken); // Must be TwoFactorToken

        $this->assertRedirectToAuthForm();

        $this->listener->handle($this->getResponseEvent);
    }

    /**
     * @test
     */
    public function handle_twoFactorProcessComplete_returnResponseFromSuccessHandler()
    {
        $this->stubTokenManagerHasToken($this->createTwoFactorToken());
        $this->stubCurrentPath(self::CHECK_PATH);
        $this->stubAuthenticationManagerReturnsToken($this->createMock(TokenInterface::class)); // Not a TwoFactorToken

        $response = $this->createMock(Response::class);
        $this->successHandler
            ->expects($this->once())
            ->method('onAuthenticationSuccess')
            ->willReturn($response);

        $this->getResponseEvent
            ->expects($this->once())
            ->method('setResponse')
            ->with($this->identicalTo($response));

        $this->listener->handle($this->getResponseEvent);
    }

    /**
     * @test
     */
    public function handle_twoFactorProcessComplete_dispatchCompleteEvent()
    {
        $this->stubTokenManagerHasToken($this->createTwoFactorToken());
        $this->stubCurrentPath(self::CHECK_PATH);
        $this->stubAuthenticationManagerReturnsToken($this->createMock(TokenInterface::class)); // Not a TwoFactorToken
        $this->stubHandlersReturnResponse();

        $this->assertEventsDispatched([
            TwoFactorAuthenticationEvents::ATTEMPT,
            TwoFactorAuthenticationEvents::SUCCESS,
            TwoFactorAuthenticationEvents::COMPLETE,
        ]);

        $this->listener->handle($this->getResponseEvent);
    }

    /**
     * @test
     */
    public function handle_twoFactorProcessCompleteWithTrustedEnabled_setTrustedDevice()
    {
        $authenticatedToken = $this->createMock(TokenInterface::class);
        $authenticatedToken
            ->expects($this->any())
            ->method('getUser')
            ->willReturn('user');

        $this->stubTokenManagerHasToken($this->createTwoFactorToken());
        $this->stubCurrentPath(self::CHECK_PATH);
        $this->stubRequestHasParameter(self::TRUSTED_PARAM, '1');
        $this->stubAuthenticationManagerReturnsToken($authenticatedToken); // Not a TwoFactorToken
        $this->stubHandlersReturnResponse();

        $this->trustedDeviceManager
            ->expects($this->once())
            ->method('addTrustedDevice')
            ->with('user', 'firewallName');

        $this->listener->handle($this->getResponseEvent);
    }

    /**
     * @test
     */
    public function handle_twoFactorProcessCompleteWithTrustedDisabled_notSetTrustedDevice()
    {
        $this->stubTokenManagerHasToken($this->createTwoFactorToken());
        $this->stubCurrentPath(self::CHECK_PATH);
        $this->stubRequestHasParameter(self::TRUSTED_PARAM, '0');
        $this->stubAuthenticationManagerReturnsToken($this->createMock(TokenInterface::class)); // Not a TwoFactorToken
        $this->stubHandlersReturnResponse();

        $this->trustedDeviceManager
            ->expects($this->never())
            ->method($this->anything());

        $this->listener->handle($this->getResponseEvent);
    }
}
