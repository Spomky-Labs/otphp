<?php
namespace Scheb\TwoFactorBundle\Security\Http\Firewall;

use Psr\Log\LoggerInterface;
use Scheb\TwoFactorBundle\Security\Authentication\Token\TwoFactorToken;
use Scheb\TwoFactorBundle\Security\TwoFactor\Event\TwoFactorAuthenticationEvent;
use Scheb\TwoFactorBundle\Security\TwoFactor\Event\TwoFactorAuthenticationEvents;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\Security\Core\Authentication\AuthenticationManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authentication\AuthenticationFailureHandlerInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;
use Symfony\Component\Security\Http\Firewall\ListenerInterface;
use Symfony\Component\Security\Http\HttpUtils;
use Symfony\Component\Security\Http\Util\TargetPathTrait;

class TwoFactorListener implements ListenerInterface
{
    use TargetPathTrait;

    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;

    /**
     * @var AuthenticationManagerInterface
     */
    private $authenticationManager;

    /**
     * @var string
     */
    private $providerKey;

    /**
     * @var AuthenticationSuccessHandlerInterface
     */
    private $successHandler;

    /**
     * @var AuthenticationFailureHandlerInterface
     */
    private $failureHandler;

    /**
     * @var EventDispatcherInterface
     */
    private $dispatcher;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var string[]
     */
    private $options;

    /**
     * @var HttpUtils
     */
    private $httpUtils;

    /**
     * Constructor.
     *
     * @param TokenStorageInterface                  $tokenStorage          A TokenStorageInterface instance
     * @param AuthenticationManagerInterface         $authenticationManager An AuthenticationManagerInterface instance
     * @param HttpUtils                              $httpUtils             An HttpUtilsInterface instance
     * @param string                                 $providerKey
     * @param AuthenticationSuccessHandlerInterface  $successHandler
     * @param AuthenticationFailureHandlerInterface  $failureHandler
     * @param array                                  $options               An array of options for the processing of a
     *                                                                      successful, or failed authentication attempt
     * @param LoggerInterface                        $logger                A LoggerInterface instance
     * @param EventDispatcherInterface               $dispatcher            An EventDispatcherInterface instance
     *
     * @throws \InvalidArgumentException
     */
    public function __construct(
        TokenStorageInterface $tokenStorage,
        AuthenticationManagerInterface $authenticationManager,
        HttpUtils $httpUtils,
        $providerKey,
        AuthenticationSuccessHandlerInterface $successHandler,
        AuthenticationFailureHandlerInterface $failureHandler,
        array $options,
        LoggerInterface $logger,
        EventDispatcherInterface $dispatcher
    )
    {
        if (empty($providerKey)) {
            throw new \InvalidArgumentException('$providerKey must not be empty.');
        }

        $this->tokenStorage = $tokenStorage;
        $this->authenticationManager = $authenticationManager;
        $this->providerKey = $providerKey;
        $this->successHandler = $successHandler;
        $this->failureHandler = $failureHandler;
        $this->options = array_merge(array(
            'check_path' => '/login_check',
            'auth_form_path' => '/login',
            'auth_code_parameter_name' => '_auth_code',
            'always_use_default_target_path' => false,
            'default_target_path' => '/',
        ), $options);
        $this->logger = $logger;
        $this->dispatcher = $dispatcher;
        $this->httpUtils = $httpUtils;
    }

    /**
     * @param GetResponseEvent $event
     */
    public function handle(GetResponseEvent $event)
    {
        $currentToken = $this->tokenStorage->getToken();
        if (!($currentToken instanceof TwoFactorToken && $currentToken->getProviderKey() === $this->providerKey)) {
            return;
        }

        $request = $event->getRequest();
        if ($this->isCheckAuthCodeRequest($request)) {
            $response = $this->attemptAuthentication($request, $currentToken);
            $event->setResponse($response);
            return;
        }

        if (!$this->isAuthFormRequest($request)) {
            $response = $this->redirectToAuthForm($request);
            $this->setTargetPath($request);
            $event->setResponse($response);
            return;
        }
    }

    /**
     * @param Request $request
     *
     * @return bool
     */
    private function isCheckAuthCodeRequest(Request $request)
    {
        return $this->httpUtils->checkRequestPath($request, $this->options['check_path']);
    }

    /**
     * @param Request $request
     *
     * @return bool
     */
    private function isAuthFormRequest(Request $request)
    {
        return $this->httpUtils->checkRequestPath($request, $this->options['auth_form_path']);
    }

    /**
     * @param Request $request
     *
     * @return Response
     */
    private function redirectToAuthForm($request) {
        return $this->httpUtils->createRedirectResponse($request, $this->options['auth_form_path']);
    }

    /**
     * @param Request $request
     */
    private function setTargetPath(Request $request)
    {
        // session isn't required when using HTTP basic authentication mechanism for example
        if ($request->hasSession() && $request->isMethodSafe(false) && !$request->isXmlHttpRequest()) {
            $this->saveTargetPath($request->getSession(), $this->providerKey, $request->getUri());
        }
    }

    /**
     * @param Request $request
     * @param TwoFactorToken $currentToken
     *
     * @return Response
     */
    private function attemptAuthentication(Request $request, TwoFactorToken $currentToken)
    {
        $authCode = $request->get($this->options['auth_code_parameter_name'], '');
        try {
            $token = new TwoFactorToken($currentToken->getAuthenticatedToken(), $authCode, $this->providerKey);
            $resultToken = $this->authenticationManager->authenticate($token);
            return $this->onSuccess($request, $resultToken);
        } catch (AuthenticationException $failed) {
            return $this->onFailure($request, $failed);
        }
    }

    /**
     * @param Request $request
     * @param AuthenticationException $failed
     *
     * @return Response
     */
    private function onFailure(Request $request, AuthenticationException $failed)
    {
        $this->logger->info('Two-factor authentication request failed.', array('exception' => $failed));

        $token = $this->tokenStorage->getToken();
        $loginEvent = new TwoFactorAuthenticationEvent($request, $token);
        $this->dispatcher->dispatch(TwoFactorAuthenticationEvents::FAILURE, $loginEvent);

        $response = $this->failureHandler->onAuthenticationFailure($request, $failed);
        if (!$response instanceof Response) {
            throw new \RuntimeException('Authentication Failure Handler did not return a Response.');
        }

        return $response;
    }

    /**
     * @param Request $request
     * @param TokenInterface $token
     *
     * @return Response
     */
    private function onSuccess(Request $request, TokenInterface $token)
    {
        $this->logger->info('User has been two-factor authenticated successfully.', array('username' => $token->getUsername()));

        $this->tokenStorage->setToken($token);

        // When it's still a TwoFactorToken, keep showing the auth form
        if ($token instanceof TwoFactorToken) {
            return $this->redirectToAuthForm($request);
        }

        $loginEvent = new TwoFactorAuthenticationEvent($request, $token);
        $this->dispatcher->dispatch(TwoFactorAuthenticationEvents::SUCCESS, $loginEvent);

        $response = $this->successHandler->onAuthenticationSuccess($request, $token);
        if (!$response instanceof Response) {
            throw new \RuntimeException('Authentication Success Handler did not return a Response.');
        }

        return $response;
    }
}

