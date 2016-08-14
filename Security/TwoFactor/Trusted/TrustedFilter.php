<?php

namespace Scheb\TwoFactorBundle\Security\TwoFactor\Trusted;

use Symfony\Component\HttpFoundation\Response;
use Scheb\TwoFactorBundle\Security\TwoFactor\AuthenticationHandlerInterface;
use Scheb\TwoFactorBundle\Security\TwoFactor\AuthenticationContextInterface;

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

    /**
     * Construct the trusted computer layer.
     *
     * @param AuthenticationHandlerInterface $authHandler
     * @param TrustedCookieManager           $cookieManager
     * @param bool                           $useTrustedOption
     * @param string                         $trustedName
     */
    public function __construct(AuthenticationHandlerInterface $authHandler, TrustedCookieManager $cookieManager, $useTrustedOption, $trustedName)
    {
        $this->authHandler = $authHandler;
        $this->cookieManager = $cookieManager;
        $this->useTrustedOption = $useTrustedOption;
        $this->trustedName = $trustedName;
    }

    /**
     * Check if user is on a trusted computer, otherwise call TwoFactorProviderRegistry.
     *
     * @param AuthenticationContextInterface $context
     */
    public function beginAuthentication(AuthenticationContextInterface $context)
    {
        $request = $context->getRequest();
        $user = $context->getUser();

        // Skip two-factor authentication on trusted computers
        if ($this->useTrustedOption && $this->cookieManager->isTrustedComputer($request, $user)) {
            return;
        }

        $context->setUseTrustedOption($this->useTrustedOption); // Set trusted flag
        $this->authHandler->beginAuthentication($context);
    }

    /**
     * Call TwoFactorProviderRegistry, set trusted computer cookie if requested.
     *
     * @param AuthenticationContextInterface $context
     *
     * @return Response|null
     */
    public function requestAuthenticationCode(AuthenticationContextInterface $context)
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
