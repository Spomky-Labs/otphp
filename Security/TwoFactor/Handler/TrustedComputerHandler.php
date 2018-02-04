<?php

namespace Scheb\TwoFactorBundle\Security\TwoFactor\Handler;

use Scheb\TwoFactorBundle\Security\TwoFactor\AuthenticationContextInterface;
use Scheb\TwoFactorBundle\Security\TwoFactor\Trusted\TrustedCookieManager;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class TrustedComputerHandler implements AuthenticationHandlerInterface
{
    /**
     * @var AuthenticationHandlerInterface
     */
    private $authenticationHandler;

    /**
     * @var TrustedCookieManager
     */
    private $cookieManager;

    /**
     * @var string
     */
    private $trustedName;

    public function __construct(AuthenticationHandlerInterface $authenticationHandler, TrustedCookieManager $cookieManager, string $trustedName)
    {
        $this->authenticationHandler = $authenticationHandler;
        $this->cookieManager = $cookieManager;
        $this->trustedName = $trustedName;
    }

    public function beginTwoFactorAuthentication(AuthenticationContextInterface $context): TokenInterface
    {
        $request = $context->getRequest();
        $user = $context->getUser();

        // Skip two-factor authentication on trusted computers
        if ($context->useTrustedOption() && $this->cookieManager->isTrustedComputer($request, $user)) {
            // TODO: refresh trusted token
            return $context->getToken();
        }

        return $this->authenticationHandler->beginTwoFactorAuthentication($context);
    }
}
