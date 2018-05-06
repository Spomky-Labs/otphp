<?php

namespace Scheb\TwoFactorBundle\Security\Http\Firewall;

use Psr\Log\LoggerInterface;
use Scheb\TwoFactorBundle\DependencyInjection\Factory\Security\TwoFactorFactory;
use Scheb\TwoFactorBundle\Security\Authentication\Token\TwoFactorToken;
use Scheb\TwoFactorBundle\Security\TwoFactor\Event\TwoFactorAuthenticationEvent;
use Scheb\TwoFactorBundle\Security\TwoFactor\Event\TwoFactorAuthenticationEvents;
use Scheb\TwoFactorBundle\Security\TwoFactor\Trusted\TrustedDeviceManagerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\Security\Core\Authentication\AuthenticationManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\AccessMapInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationFailureHandlerInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;
use Symfony\Component\Security\Http\Firewall\ListenerInterface;
use Symfony\Component\Security\Http\HttpUtils;
use Symfony\Component\Security\Http\Util\TargetPathTrait;

class TwoFactorListener implements ListenerInterface
{
    use TargetPathTrait;

    private const DEFAULT_OPTIONS = [
        'auth_form_path' => TwoFactorFactory::DEFAULT_AUTH_FORM_PATH,
        'check_path' => TwoFactorFactory::DEFAULT_CHECK_PATH,
        'auth_code_parameter_name' => TwoFactorFactory::DEFAULT_AUTH_CODE_PARAMETER_NAME,
        'trusted_parameter_name' => TwoFactorFactory::DEFAULT_TRUSTED_PARAMETER_NAME,
    ];

    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;

    /**
     * @var AuthenticationManagerInterface
     */
    private $authenticationManager;

    /**
     * @var HttpUtils
     */
    private $httpUtils;

    /**
     * @var string
     */
    private $firewallName;

    /**
     * @var AuthenticationSuccessHandlerInterface
     */
    private $successHandler;

    /**
     * @var AuthenticationFailureHandlerInterface
     */
    private $failureHandler;

    /**
     * @var string[]
     */
    private $options;

    /**
     * @var TrustedDeviceManagerInterface
     */
    private $trustedDeviceManager;

    /**
     * @var AccessMapInterface
     */
    private $accessMap;

    /**
     * @var AccessDecisionManagerInterface
     */
    private $accessDecisionManager;

    /**
     * @var EventDispatcherInterface
     */
    private $dispatcher;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        TokenStorageInterface $tokenStorage,
        AuthenticationManagerInterface $authenticationManager,
        HttpUtils $httpUtils,
        string $firewallName,
        AuthenticationSuccessHandlerInterface $successHandler,
        AuthenticationFailureHandlerInterface $failureHandler,
        array $options,
        TrustedDeviceManagerInterface $trustedDeviceManager,
        AccessMapInterface $accessMap,
        AccessDecisionManagerInterface $accessDecisionManager,
        EventDispatcherInterface $dispatcher,
        ?LoggerInterface $logger = null
    ) {
        if (empty($firewallName)) {
            throw new \InvalidArgumentException('$firewallName must not be empty.');
        }

        $this->tokenStorage = $tokenStorage;
        $this->authenticationManager = $authenticationManager;
        $this->httpUtils = $httpUtils;
        $this->firewallName = $firewallName;
        $this->successHandler = $successHandler;
        $this->failureHandler = $failureHandler;
        $this->options = array_merge(self::DEFAULT_OPTIONS, $options);
        $this->dispatcher = $dispatcher;
        $this->logger = $logger;
        $this->trustedDeviceManager = $trustedDeviceManager;
        $this->accessMap = $accessMap;
        $this->accessDecisionManager = $accessDecisionManager;
    }

    public function handle(GetResponseEvent $event)
    {
        $currentToken = $this->tokenStorage->getToken();
        if (!($currentToken instanceof TwoFactorToken && $currentToken->getProviderKey() === $this->firewallName)) {
            return;
        }

        $request = $event->getRequest();
        if ($this->isCheckAuthCodeRequest($request)) {
            $response = $this->attemptAuthentication($request, $currentToken);
            $event->setResponse($response);

            return;
        }

        // Let routes pass, e.g. if a route needs to be callable during two-factor authentication
        list($attributes) = $this->accessMap->getPatterns($request);
        if (null !== $attributes && $this->accessDecisionManager->decide($currentToken, $attributes, $request)) {
            return;
        }

        if (!$this->isAuthFormRequest($request)) {
            $response = $this->redirectToAuthForm($request);
            $this->setTargetPath($request);
            $event->setResponse($response);

            return;
        }
    }

    private function isCheckAuthCodeRequest(Request $request): bool
    {
        return $this->httpUtils->checkRequestPath($request, $this->options['check_path']);
    }

    private function isAuthFormRequest(Request $request): bool
    {
        return $this->httpUtils->checkRequestPath($request, $this->options['auth_form_path']);
    }

    private function redirectToAuthForm(Request $request): RedirectResponse
    {
        return $this->httpUtils->createRedirectResponse($request, $this->options['auth_form_path']);
    }

    private function setTargetPath(Request $request): void
    {
        // session isn't required when using HTTP basic authentication mechanism for example
        if ($request->hasSession() && $request->isMethodSafe(false) && !$request->isXmlHttpRequest()) {
            $this->saveTargetPath($request->getSession(), $this->firewallName, $request->getUri());
        }
    }

    private function attemptAuthentication(Request $request, TwoFactorToken $currentToken): Response
    {
        $authCode = $request->get($this->options['auth_code_parameter_name'], '');
        try {
            $token = new TwoFactorToken($currentToken->getAuthenticatedToken(), $authCode, $this->firewallName, $currentToken->getTwoFactorProviders());
            $this->dispatchLoginEvent(TwoFactorAuthenticationEvents::ATTEMPT, $request, $token);
            $resultToken = $this->authenticationManager->authenticate($token);

            return $this->onSuccess($request, $resultToken);
        } catch (AuthenticationException $failed) {
            return $this->onFailure($request, $failed);
        }
    }

    private function onFailure(Request $request, AuthenticationException $failed): Response
    {
        if ($this->logger) {
            $this->logger->info('Two-factor authentication request failed.', ['exception' => $failed]);
        }
        $this->dispatchLoginEvent(TwoFactorAuthenticationEvents::FAILURE, $request, $this->tokenStorage->getToken());

        $response = $this->failureHandler->onAuthenticationFailure($request, $failed);

        return $response;
    }

    private function onSuccess(Request $request, TokenInterface $token): Response
    {
        if ($this->logger) {
            $this->logger->info('User has been two-factor authenticated successfully.', ['username' => $token->getUsername()]);
        }
        $this->tokenStorage->setToken($token);
        $this->dispatchLoginEvent(TwoFactorAuthenticationEvents::SUCCESS, $request, $token);

        // When it's still a TwoFactorToken, keep showing the auth form
        if ($token instanceof TwoFactorToken) {
            return $this->redirectToAuthForm($request);
        }

        $this->dispatchLoginEvent(TwoFactorAuthenticationEvents::COMPLETE, $request, $token);

        if ($this->hasTrustedDeviceParameter($request)) {
            $this->trustedDeviceManager->addTrustedDevice($token->getUser(), $this->firewallName);
        }

        $response = $this->successHandler->onAuthenticationSuccess($request, $token);

        return $response;
    }

    private function hasTrustedDeviceParameter(Request $request): bool
    {
        return (bool) $request->get($this->options['trusted_parameter_name'], false);
    }

    private function dispatchLoginEvent(string $eventType, Request $request, TokenInterface $token): void
    {
        $event = new TwoFactorAuthenticationEvent($request, $token);
        $this->dispatcher->dispatch($eventType, $event);
    }
}
