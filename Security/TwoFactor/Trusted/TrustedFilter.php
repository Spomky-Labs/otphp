<?php
namespace Scheb\TwoFactorBundle\Security\TwoFactor\Trusted;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Scheb\TwoFactorBundle\Security\TwoFactor\Provider\TwoFactorProviderRegistry;
use Scheb\TwoFactorBundle\Security\TwoFactor\AuthenticationContext;
use Scheb\TwoFactorBundle\Model\TrustedComputerInterface;

class TrustedFilter
{

    /**
     * @var \Scheb\TwoFactorBundle\Security\TwoFactor\Provider\TwoFactorProviderRegistry $registry
     */
    private $provider;

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
     * @param \Scheb\TwoFactorBundle\Security\TwoFactor\Provider\TwoFactorProviderRegistry $registry
     * @param \Scheb\TwoFactorBundle\Security\TwoFactor\Trusted\TrustedCookieManager       $cookieManager
     * @param boolean                                                                      $useTrustedOption
     * @param string                                                                       $trustedName
     */
    public function __construct(TwoFactorProviderRegistry $registry, TrustedCookieManager $cookieManager, $useTrustedOption, $trustedName)
    {
        $this->registry = $registry;
        $this->cookieManager = $cookieManager;
        $this->useTrustedOption = $useTrustedOption;
        $this->trustedName = $trustedName;
    }

    /**
     * Check if user is on a trusted computer, otherwise call TwoFactorProviderRegistry
     *
     * @param \Symfony\Component\HttpFoundation\Request                            $request
     * @param \Symfony\Component\Security\Core\Authentication\Token\TokenInterface $token
     */
    public function beginAuthentication(Request $request, TokenInterface $token)
    {
        $user = $token->getUser();

        // Skip two factor authentication on trusted computers
        if ($this->useTrustedOption($request, $user) && $this->cookieManager->isTrustedComputer($request, $user)) {
            return;
        }

        $context = $this->createAuthenticationContext($request, $token);
        $this->registry->beginAuthentication($context);
    }

    /**
     * Call TwoFactorProviderRegistry, set trusted computer cookie if requested
     *
     * @param  \Symfony\Component\HttpFoundation\Request                            $request
     * @param  \Symfony\Component\Security\Core\Authentication\Token\TokenInterface $token
     * @return \Symfony\Component\HttpFoundation\Response|null
     */
    public function requestAuthenticationCode(Request $request, TokenInterface $token)
    {
        $context = $this->createAuthenticationContext($request, $token);
        $response = $this->registry->requestAuthenticationCode($context);

        // On reponse validate if trusted cookie should be set
        if ($response instanceof Response) {

            // Set trusted cookie
            if ($context->isAuthenticated() && $request->get($this->trustedName)) {
                $cookie = $this->cookieManager->createTrustedCookie($request, $context->getUser());
                $response->headers->setCookie($cookie);
            }

            return $response;
        }

        return null;
    }

    /**
     * Create AuthenticationContext object
     *
     * @param  \Symfony\Component\HttpFoundation\Request                            $request
     * @param  \Symfony\Component\Security\Core\Authentication\Token\TokenInterface $token
     * @return \Scheb\TwoFactorBundle\Security\TwoFactor\AuthenticationContext
     */
    private function createAuthenticationContext(Request $request, TokenInterface $token)
    {
        return new AuthenticationContext($request, $token, $this->useTrustedOption($request, $token->getUser()));
    }

    /**
     * Return true when trusted computer feature can be used
     *
     * @param  \Symfony\Component\HttpFoundation\Request $request
     * @param  mixed                                     $user
     * @return boolean
     */
    private function useTrustedOption(Request $request, $user)
    {
        return $this->useTrustedOption && $user instanceof TrustedComputerInterface;
    }

}
