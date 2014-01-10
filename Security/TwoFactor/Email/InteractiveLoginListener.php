<?php
namespace Scheb\TwoFactorBundle\Security\TwoFactor\Email;

use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Scheb\TwoFactorBundle\Model\Email\TwoFactorInterface;

class InteractiveLoginListener
{

    /**
     * @var \Scheb\TwoFactorBundle\Security\TwoFactor\Email\AuthCodeManager $codeManager
     */
    private $codeManager;

    /**
     * Construct a listener, which is handling successful authentication
     *
     * @param \Scheb\TwoFactorBundle\Security\TwoFactor\Email\AuthCodeManager $codeManager
     */
    public function __construct(AuthCodeManager $codeManager)
    {
        $this->codeManager = $codeManager;
    }

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
        if (! $user->isEmailAuthEnabled()) {
            return;
        }

        // Set flag in the session
        $sessionFlag = sprintf('two_factor_email_%s_%s', $token->getProviderKey(), $token->getUsername());
        $event->getRequest()->getSession()->set($sessionFlag, null);

        // Generate and send a new security code
        $this->codeManager->generateAndSend($user);
    }
}
