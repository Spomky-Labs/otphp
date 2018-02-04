<?php

namespace Scheb\TwoFactorBundle\Security\TwoFactor\Handler;

use Scheb\TwoFactorBundle\Security\TwoFactor\AuthenticationContextInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class IpWhitelistHandler implements AuthenticationHandlerInterface
{
    /**
     * @var AuthenticationHandlerInterface
     */
    private $authenticationHandler;

    /**
     * @var string[]
     */
    private $ipWhitelist;

    public function __construct(AuthenticationHandlerInterface $authenticationHandler, array $ipWhitelist)
    {
        $this->authenticationHandler = $authenticationHandler;
        $this->ipWhitelist = $ipWhitelist;
    }

    public function beginTwoFactorAuthentication(AuthenticationContextInterface $context): TokenInterface
    {
        $request = $context->getRequest();

        // Skip two-factor authentication for whitelisted IPs
        if (in_array($request->getClientIp(), $this->ipWhitelist)) {
            return $context->getToken();
        }

        return $this->authenticationHandler->beginTwoFactorAuthentication($context);
    }
}
