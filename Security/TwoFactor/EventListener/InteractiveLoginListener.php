<?php
namespace Scheb\TwoFactorBundle\Security\TwoFactor\EventListener;

use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Scheb\TwoFactorBundle\Model\Email\TwoFactorInterface;
use Scheb\TwoFactorBundle\Security\TwoFactor\Provider\TwoFactorProvider;

class InteractiveLoginListener
{

    /**
     *
     * @var \Scheb\TwoFactorBundle\Security\TwoFactor\Provider\TwoFactorProvider $registry
     */
    private $provider;

    /**
     * Construct a listener for login events
     *
     * @param \Scheb\TwoFactorBundle\Security\TwoFactor\Provider\TwoFactorProvider $registry
     */
    public function __construct(TwoFactorProvider $provider)
    {
        $this->provider = $provider;
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
        if (! $token instanceof UsernamePasswordToken) {
            return;
        }

        // Forward to two factor providers
        // They decide if they will do two-factor authentication
        $this->provider->beginAuthentication($request, $token);
    }
}
