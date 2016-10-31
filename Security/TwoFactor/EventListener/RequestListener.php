<?php

namespace Scheb\TwoFactorBundle\Security\TwoFactor\EventListener;

use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpFoundation\Response;
use Scheb\TwoFactorBundle\Security\TwoFactor\AuthenticationHandlerInterface;
use Scheb\TwoFactorBundle\Security\TwoFactor\AuthenticationContextFactoryInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class RequestListener
{
    /**
     * @var AuthenticationContextFactoryInterface
     */
    private $authenticationContextFactory;

    /**
     * @var AuthenticationHandlerInterface
     */
    private $authHandler;

    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;

    /**
     * @var array
     */
    private $supportedTokens;

    /**
     * @var string
     */
    private $excludePattern;

    /**
     * Construct a listener for login events.
     *
     * @param AuthenticationContextFactoryInterface $authenticationContextFactory
     * @param AuthenticationHandlerInterface        $authHandler
     * @param TokenStorageInterface                 $tokenStorage
     * @param array                                 $supportedTokens
     * @param string                                $excludePattern
     */
    public function __construct(
        AuthenticationContextFactoryInterface $authenticationContextFactory,
        AuthenticationHandlerInterface $authHandler,
        TokenStorageInterface $tokenStorage,
        array $supportedTokens,
        $excludePattern
    ) {
        $this->authenticationContextFactory = $authenticationContextFactory;
        $this->authHandler = $authHandler;
        $this->tokenStorage = $tokenStorage;
        $this->supportedTokens = $supportedTokens;
        $this->excludePattern = $excludePattern;
    }

    /**
     * Listen for request events.
     *
     * @param GetResponseEvent $event
     */
    public function onCoreRequest(GetResponseEvent $event)
    {
        $request = $event->getRequest();

        // Exclude path
        if ($this->excludePattern !== null && preg_match('#'.$this->excludePattern.'#', $request->getPathInfo())) {
            return;
        }

        // Check if security token is supported
        $token = $this->tokenStorage->getToken();
        if (!$this->isTokenSupported($token)) {
            return;
        }

        // Forward to two-factor provider
        // Providers can create a response object
        $context = $this->authenticationContextFactory->create($request, $token);
        $response = $this->authHandler->requestAuthenticationCode($context);

        // Set the response (if there is one)
        if ($response instanceof Response) {
            $event->setResponse($response);
        }
    }

    /**
     * Check if the token class is supported.
     *
     * @param mixed $token
     *
     * @return bool
     */
    private function isTokenSupported($token)
    {
        if (null === $token) {
            return false;
        }

        $class = get_class($token);

        return in_array($class, $this->supportedTokens);
    }
}
