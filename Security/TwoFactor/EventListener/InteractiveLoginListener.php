<?php
namespace Scheb\TwoFactorBundle\Security\TwoFactor\EventListener;

use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Scheb\TwoFactorBundle\Security\TwoFactor\Provider\TwoFactorProviderRegistry;

class InteractiveLoginListener
{

    /**
     * @var \Scheb\TwoFactorBundle\Security\TwoFactor\Provider\TwoFactorProviderRegistry $registry
     */
    private $registry;

    /**
     * @var array $supportedTokens
     */
    private $supportedTokens;

    /**
     * Construct a listener for login events
     *
     * @param \Scheb\TwoFactorBundle\Security\TwoFactor\Provider\TwoFactorProviderRegistry $registry
     * @param array                                                                        $supportedTokens
     */
    public function __construct(TwoFactorProviderRegistry $registry, array $supportedTokens)
    {
        $this->registry = $registry;
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
        $token = $event->getAuthenticationToken();
        if (!$this->isTokenSupported($token)) {
            return;
        }

        // Forward to two factor providers
        // They decide if they will do two-factor authentication
        $this->registry->beginAuthentication($request, $token);
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
