<?php
namespace Scheb\TwoFactorBundle\Security\TwoFactor\Trusted;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Scheb\TwoFactorBundle\Security\TwoFactor\AuthenticationHandlerInterface;
use Scheb\TwoFactorBundle\Security\TwoFactor\Provider\TwoFactorProviderRegistry;
use Scheb\TwoFactorBundle\Security\TwoFactor\AuthenticationContext;
use Scheb\TwoFactorBundle\Model\TrustedComputerInterface;

class TrustedFilter implements AuthenticationHandlerInterface
{

    /**
     * @var \Scheb\TwoFactorBundle\Security\TwoFactor\AuthenticationHandlerInterface $authHandler
     */
    private $authHandler;

    /**
     * Manages trusted computer cookies
     *
     * @var \Scheb\TwoFactorBundle\Security\TwoFactor\Trusted\TrustedCookieManager $cookieManager
     */
    private $cookieManager;

    /**
     * If trusted computer feature is enabled
     *
     * @var boolean $useTrustedOption
     */
    private $useTrustedOption;

    /**
     * @var string $trustedName
     */
    private $trustedName;

    /**
     * Construct the trusted computer layer
     *
     * @param \Scheb\TwoFactorBundle\Security\TwoFactor\AuthenticationHandlerInterface $authHandler
     * @param \Scheb\TwoFactorBundle\Security\TwoFactor\Trusted\TrustedCookieManager   $cookieManager
     * @param boolean                                                                  $useTrustedOption
     * @param string                                                                   $trustedName
     */
    public function __construct(AuthenticationHandlerInterface $authHandler, TrustedCookieManager $cookieManager, $useTrustedOption, $trustedName)
    {
        $this->authHandler = $authHandler;
        $this->cookieManager = $cookieManager;
        $this->useTrustedOption = $useTrustedOption;
        $this->trustedName = $trustedName;
    }

    /**
     * Check if user is on a trusted computer, otherwise call TwoFactorProviderRegistry
     *
     * @param \Scheb\TwoFactorBundle\Security\TwoFactor\AuthenticationContext $context
     */
    public function beginAuthentication(AuthenticationContext $context)
    {
        $request = $context->getRequest();
        $user = $context->getUser();
        $useTrustedOption = $this->useTrustedOption($user);

        // Skip two factor authentication on trusted computers
        if ($useTrustedOption && $this->cookieManager->isTrustedComputer($request, $user)) {
            return;
        }

        $context->setUseTrustedOption($useTrustedOption); // Set trusted flag
        $this->authHandler->beginAuthentication($context);
    }

    /**
     * Call TwoFactorProviderRegistry, set trusted computer cookie if requested
     *
     * @param  \Scheb\TwoFactorBundle\Security\TwoFactor\AuthenticationContext $context
     * @return \Symfony\Component\HttpFoundation\Response|null
     */
    public function requestAuthenticationCode(AuthenticationContext $context)
    {
        $request = $context->getRequest();
        $user = $context->getUser();

        $context->setUseTrustedOption($this->useTrustedOption($user)); // Set trusted flag
        $response = $this->authHandler->requestAuthenticationCode($context);

        // On response validate if trusted cookie should be set
        if ($response instanceof Response) {

            // Set trusted cookie
            if ($context->isAuthenticated() && $request->get($this->trustedName)) {
                $cookie = $this->cookieManager->createTrustedCookie($request, $user);
                $response->headers->setCookie($cookie);
            }

            return $response;
        }

        return null;
    }

    /**
     * Return true when trusted computer feature can be used
     *
     * @param  mixed   $user
     * @return boolean
     */
    private function useTrustedOption($user)
    {
        return $this->useTrustedOption && $user instanceof TrustedComputerInterface;
    }

}
