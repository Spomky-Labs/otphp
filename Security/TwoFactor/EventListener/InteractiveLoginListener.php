<?php
namespace Scheb\TwoFactorBundle\Security\TwoFactor\EventListener;

use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Scheb\TwoFactorBundle\Security\TwoFactor\Provider\TwoFactorProvider;

class InteractiveLoginListener
{

    /**
     * @var \Scheb\TwoFactorBundle\Security\TwoFactor\Provider\TwoFactorProvider $registry
     */
    private $provider;

    /**
     * @var array $supportedTokens
     */
    private $supportedTokens;

    /**
     * Construct a listener for login events
     *
     * @param \Scheb\TwoFactorBundle\Security\TwoFactor\Provider\TwoFactorProvider $registry
     * @param array                                                                $supportedTokens
     */
    public function __construct(TwoFactorProvider $provider, array $supportedTokens)
    {
        $this->provider = $provider;
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
        $this->provider->beginAuthentication($request, $token);
    }

    /**
     * Check if the token class is supported
     *
     * @param  mixed   $token
     * @return boolean
     */
    public function isTokenSupported($token)
    {
        $class = get_class($token);

        return in_array($class, $this->supportedTokens);
    }
}
