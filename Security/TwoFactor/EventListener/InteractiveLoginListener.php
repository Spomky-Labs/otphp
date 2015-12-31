<?php

namespace Scheb\TwoFactorBundle\Security\TwoFactor\EventListener;

use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Scheb\TwoFactorBundle\Security\TwoFactor\AuthenticationContext;
use Scheb\TwoFactorBundle\Security\TwoFactor\AuthenticationHandlerInterface;

class InteractiveLoginListener
{
    /**
     * @var AuthenticationHandlerInterface
     */
    private $authHandler;

    /**
     * @var array
     */
    private $supportedTokens;

    /**
     * Construct a listener for login events.
     *
     * @param AuthenticationHandlerInterface $authHandler
     * @param array                          $supportedTokens
     */
    public function __construct(AuthenticationHandlerInterface $authHandler, array $supportedTokens)
    {
        $this->authHandler = $authHandler;
        $this->supportedTokens = $supportedTokens;
    }

    /**
     * Listen for successful login events.
     *
     * @param InteractiveLoginEvent $event
     */
    public function onSecurityInteractiveLogin(InteractiveLoginEvent $event)
    {
        $request = $event->getRequest();

        // Check if security token is supported
        $token = $event->getAuthenticationToken();
        if (!$this->isTokenSupported($token)) {
            return;
        }

        // Forward to two-factor providers
        // They decide if they will do two-factor authentication
        $context = new AuthenticationContext($request, $token);
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
