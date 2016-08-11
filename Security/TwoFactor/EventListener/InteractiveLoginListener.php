<?php

namespace Scheb\TwoFactorBundle\Security\TwoFactor\EventListener;

use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Scheb\TwoFactorBundle\Security\TwoFactor\AuthenticationHandlerInterface;
use Scheb\TwoFactorBundle\Security\TwoFactor\AuthenticationContextFactoryInterface;

class InteractiveLoginListener
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
     * @var array
     */
    private $supportedTokens;

    /**
     * @var array
     */
    private $ipWhitelist;

    /**
     * Construct a listener for login events.
     *
     * @param AuthenticationContextFactoryInterface $authenticationContextFactory
     * @param AuthenticationHandlerInterface $authHandler
     * @param array $supportedTokens
     * @param array $ipWhitelist
     */
    public function __construct(
        AuthenticationContextFactoryInterface $authenticationContextFactory,
        AuthenticationHandlerInterface $authHandler,
        array $supportedTokens,
        array $ipWhitelist
    ) {
        $this->authenticationContextFactory = $authenticationContextFactory;
        $this->authHandler = $authHandler;
        $this->supportedTokens = $supportedTokens;
        $this->ipWhitelist = $ipWhitelist;
    }

    /**
     * Listen for successful login events.
     *
     * @param InteractiveLoginEvent $event
     */
    public function onSecurityInteractiveLogin(InteractiveLoginEvent $event)
    {
        $request = $event->getRequest();

        // Skip two-factor authentication for whitelisted IPs
        if (in_array($request->getClientIp(), $this->ipWhitelist)) {
            return;
        }

        // Check if security token is supported
        $token = $event->getAuthenticationToken();
        if (!$this->isTokenSupported($token)) {
            return;
        }

        // Forward to two-factor providers
        // They decide if they will do two-factor authentication
        $context = $this->authenticationContextFactory->create($request, $token);
        $this->authHandler->beginAuthentication($context);
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
        $class = get_class($token);

        return in_array($class, $this->supportedTokens);
    }
}
