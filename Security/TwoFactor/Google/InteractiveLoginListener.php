<?php
namespace Scheb\TwoFactorBundle\Security\TwoFactor\Google;

use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Scheb\TwoFactorBundle\Model\Google\TwoFactorInterface;

class InteractiveLoginListener
{

    /**
     * Listen for successful login events
     *
     * @param \Symfony\Component\Security\Http\Event\InteractiveLoginEvent $event
     */
    public function onSecurityInteractiveLogin(InteractiveLoginEvent $event)
    {
        if (! $event->getAuthenticationToken() instanceof UsernamePasswordToken) {
            return;
        }

        // Check if user can do two-factor authentication
        $token = $event->getAuthenticationToken();
        $user = $token->getUser();
        if (! $user instanceof TwoFactorInterface) {
            return;
        }
        if (! $user->getGoogleAuthenticatorSecret()) {
            return;
        }

        // Set flag in the session
        $sessionFlag = sprintf('two_factor_google_%s_%s', $token->getProviderKey(), $token->getUsername());
        $event->getRequest()->getSession()->set($sessionFlag, null);
    }
}
