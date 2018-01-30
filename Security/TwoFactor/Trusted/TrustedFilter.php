<?php

namespace Scheb\TwoFactorBundle\Security\TwoFactor\Trusted;

use Scheb\TwoFactorBundle\Security\TwoFactor\AuthenticationContextInterface;
use Scheb\TwoFactorBundle\Security\TwoFactor\AuthenticationHandlerInterface;
use Symfony\Component\HttpFoundation\Response;

class TrustedFilter implements AuthenticationHandlerInterface
{
    /**
     * @var AuthenticationHandlerInterface
     */
    private $authHandler;

    /**
     * Manages trusted computer cookies.
     *
     * @var TrustedCookieManager
     */
    private $cookieManager;

    /**
     * If trusted computer feature is enabled.
     *
     * @var bool
     */
    private $useTrustedOption;

    /**
     * @var string
     */
    private $trustedName;

    public function __construct(AuthenticationHandlerInterface $authHandler, TrustedCookieManager $cookieManager, bool $useTrustedOption, string $trustedName)
    {
        $this->authHandler = $authHandler;
        $this->cookieManager = $cookieManager;
        $this->useTrustedOption = $useTrustedOption;
        $this->trustedName = $trustedName;
    }

    public function beginAuthentication(AuthenticationContextInterface $context): void
    {
        $request = $context->getRequest();
        $user = $context->getUser();
        $context->setUseTrustedOption($this->useTrustedOption);

        // Skip two-factor authentication on trusted computers
        if ($context->useTrustedOption() && $this->cookieManager->isTrustedComputer($request, $user)) {
            return;
        }

        $this->authHandler->beginAuthentication($context);
    }

    public function requestAuthenticationCode(AuthenticationContextInterface $context): ?Response
    {
        $request = $context->getRequest();
        $user = $context->getUser();

        $context->setUseTrustedOption($this->useTrustedOption); // Set trusted flag
        $response = $this->authHandler->requestAuthenticationCode($context);

        // On response validate if trusted cookie should be set
        if ($response instanceof Response) {

            // Set trusted cookie
            if ($context->isAuthenticated() && $context->useTrustedOption() && $request->get($this->trustedName)) {
                $cookie = $this->cookieManager->createTrustedCookie($request, $user);
                $response->headers->setCookie($cookie);
            }

            return $response;
        }

        return null;
    }
}
