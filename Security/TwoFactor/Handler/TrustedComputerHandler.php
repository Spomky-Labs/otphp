<?php

namespace Scheb\TwoFactorBundle\Security\TwoFactor\Handler;

use Scheb\TwoFactorBundle\Security\TwoFactor\AuthenticationContextInterface;
use Scheb\TwoFactorBundle\Security\TwoFactor\Trusted\TrustedComputerManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class TrustedComputerHandler implements AuthenticationHandlerInterface
{
    /**
     * @var AuthenticationHandlerInterface
     */
    private $authenticationHandler;

    /**
     * @var TrustedComputerManagerInterface
     */
    private $trustedComputerManager;

    /**
     * @var bool
     */
    private $extendTrustedToken;

    public function __construct(
        AuthenticationHandlerInterface $authenticationHandler,
        TrustedComputerManagerInterface $trustedComputerManager,
        bool $extendTrustedToken
    )
    {
        $this->authenticationHandler = $authenticationHandler;
        $this->trustedComputerManager = $trustedComputerManager;
        $this->extendTrustedToken = $extendTrustedToken;
    }

    public function beginTwoFactorAuthentication(AuthenticationContextInterface $context): TokenInterface
    {
        $user = $context->getUser();
        $firewallName = $context->getFirewallName();

        // Skip two-factor authentication on trusted computers
        if ($context->useTrustedOption() && $this->trustedComputerManager->isTrustedComputer($user, $firewallName)) {
            if ($this->extendTrustedToken) {
                $this->trustedComputerManager->addTrustedComputer($user, $firewallName);
            }
            return $context->getToken();
        }

        return $this->authenticationHandler->beginTwoFactorAuthentication($context);
    }
}
