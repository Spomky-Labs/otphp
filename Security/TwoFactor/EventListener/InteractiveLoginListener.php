<?php
namespace Scheb\TwoFactorBundle\Security\TwoFactor\EventListener;

use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Scheb\TwoFactorBundle\Security\TwoFactor\Trusted\TrustedFilter;

class InteractiveLoginListener
{

    /**
     * @var \Scheb\TwoFactorBundle\Security\TwoFactor\Trusted\TrustedFilter $trustedFilter
     */
    private $provider;

    /**
     * @var array $supportedTokens
     */
    private $supportedTokens;

    /**
     * Construct a listener for login events
     *
     * @param \Scheb\TwoFactorBundle\Security\TwoFactor\Trusted\TrustedFilter $trustedFilter
     * @param array                                                           $supportedTokens
     */
    public function __construct(TrustedFilter $trustedFilter, array $supportedTokens)
    {
        $this->trustedFilter = $trustedFilter;
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
        $this->trustedFilter->beginAuthentication($request, $token);
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
