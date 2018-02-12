<?php

namespace Scheb\TwoFactorBundle\Security\TwoFactor\Handler;

use Scheb\TwoFactorBundle\Security\TwoFactor\AuthenticationContextInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class AuthenticatedTokenHandler implements AuthenticationHandlerInterface
{
    /**
     * @var AuthenticationHandlerInterface
     */
    private $authenticationHandler;

    /**
     * @var string[]
     */
    private $supportedTokens;

    public function __construct(AuthenticationHandlerInterface $authenticationHandler, array $supportedTokens)
    {
        $this->authenticationHandler = $authenticationHandler;
        $this->supportedTokens = $supportedTokens;
    }

    public function beginTwoFactorAuthentication(AuthenticationContextInterface $context): TokenInterface
    {
        $token = $context->getToken();

        // Check if the authenticated token is enabled for two-factor authentication
        if ($this->isTwoFactorAuthenticationEnabledForToken($token)) {
            return $this->authenticationHandler->beginTwoFactorAuthentication($context);
        }

        return $token;
    }

    private function isTwoFactorAuthenticationEnabledForToken(TokenInterface $token): bool
    {
        return in_array(get_class($token), $this->supportedTokens);
    }
}
