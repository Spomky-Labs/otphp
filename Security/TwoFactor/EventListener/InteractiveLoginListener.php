<?php
namespace Scheb\TwoFactorBundle\Security\TwoFactor\EventListener;

use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Scheb\TwoFactorBundle\Security\TwoFactor\Trusted\TrustedFilter;
use Scheb\TwoFactorBundle\Security\TwoFactor\AuthenticationContext;
use Scheb\TwoFactorBundle\Security\TwoFactor\AuthenticationHandlerInterface;

class InteractiveLoginListener
{

    /**
     * @var \Scheb\TwoFactorBundle\Security\TwoFactor\AuthenticationHandlerInterface $authHandler
     */
    private $authHandler;

    /**
     * @var \Scheb\TwoFactorBundle\Security\TwoFactor\Trusted\TrustedFilter $trustedFilter
     */
    private $trustedFilter;

    /**
     * @var array $supportedTokens
     */
    private $supportedTokens;

    /**
     * Construct a listener for login events
     *
     * @param \Scheb\TwoFactorBundle\Security\TwoFactor\AuthenticationHandlerInterface $authHandler
     * @param array                                                                    $supportedTokens
     */
    public function __construct(AuthenticationHandlerInterface $authHandler, array $supportedTokens)
    {
        $this->authHandler = $authHandler;
        $this->supportedTokens = $supportedTokens;
    }

    /**
     * Listen for successful login events
     *
     * @param \Symfony\Component\Security\Http\Event\InteractiveLoginEvent $event
     */
    public function onSecurityInteractiveLogin(InteractiveLoginEvent $event)
    {
        $request = $event->getRequest();

        // Check if security token is supported
        $token = $event->getAuthenticationToken();
        if (!$this->isTokenSupported($token)) {
            return;
        }

        // Forward to two factor providers
        // They decide if they will do two-factor authentication
        $context = new AuthenticationContext($request, $token);
        $this->authHandler->beginAuthentication($context);
    }

    /**
     * Check if the token class is supported
     *
     * @param  mixed   $token
     * @return boolean
     */
    private function isTokenSupported($token)
    {
        $class = get_class($token);

        return in_array($class, $this->supportedTokens);
    }
}
