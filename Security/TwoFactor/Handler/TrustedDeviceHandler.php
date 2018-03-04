<?php

namespace Scheb\TwoFactorBundle\Security\TwoFactor\Handler;

use Scheb\TwoFactorBundle\Security\TwoFactor\AuthenticationContextInterface;
use Scheb\TwoFactorBundle\Security\TwoFactor\Trusted\TrustedDeviceManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class TrustedDeviceHandler implements AuthenticationHandlerInterface
{
    /**
     * @var AuthenticationHandlerInterface
     */
    private $authenticationHandler;

    /**
     * @var TrustedDeviceManagerInterface
     */
    private $trustedDeviceManager;

    /**
     * @var bool
     */
    private $extendTrustedToken;

    public function __construct(
        AuthenticationHandlerInterface $authenticationHandler,
        TrustedDeviceManagerInterface $trustedDeviceManager,
        bool $extendTrustedToken
    ) {
        $this->authenticationHandler = $authenticationHandler;
        $this->trustedDeviceManager = $trustedDeviceManager;
        $this->extendTrustedToken = $extendTrustedToken;
    }

    public function beginTwoFactorAuthentication(AuthenticationContextInterface $context): TokenInterface
    {
        $user = $context->getUser();
        $firewallName = $context->getFirewallName();

        // Skip two-factor authentication on trusted devices
        if ($this->trustedDeviceManager->isTrustedDevice($user, $firewallName)) {
            if ($this->extendTrustedToken) {
                $this->trustedDeviceManager->addTrustedDevice($user, $firewallName);
            }

            return $context->getToken();
        }

        return $this->authenticationHandler->beginTwoFactorAuthentication($context);
    }
}
