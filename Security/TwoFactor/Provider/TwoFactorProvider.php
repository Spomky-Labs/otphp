<?php
namespace Scheb\TwoFactorBundle\Security\TwoFactor\Provider;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Scheb\TwoFactorBundle\Security\TwoFactor\SessionFlagManager;
use Scheb\TwoFactorBundle\Security\TwoFactor\AuthenticationContext;
use Scheb\TwoFactorBundle\Model\TrustedComputerInterface;
use Scheb\TwoFactorBundle\Security\TwoFactor\TrustedCookieManager;

class TwoFactorProvider
{

    /**
     * Manages session flags
     *
     * @var \Scheb\TwoFactorBundle\Security\TwoFactor\SessionFlagManager $flagManager
     */
    private $flagManager;

    /**
     * Manages trusted computer cookies
     *
     * @var \Scheb\TwoFactorBundle\Security\TwoFactor\TrustedCookieManager $cookieManager
     */
    private $cookieManager;

    /**
     * If trusted computer feature is enabled
     *
     * @var boolean $useTrustedOption
     */
    private $useTrustedOption;

    /**
     * List of two factor providers
     *
     * @var array $providers
     */
    private $providers;

    /**
     * Initialize with an array of registered two factor providers
     *
     * @param \Scheb\TwoFactorBundle\Security\TwoFactor\SessionFlagManager $flagManager
     * @param \Scheb\TwoFactorBundle\Security\TwoFactor\TrustedCookieManager $cookieManager
     * @param boolean $useTrustedOption
     * @param array $providers
     */
    public function __construct(SessionFlagManager $flagManager, TrustedCookieManager $cookieManager, $useTrustedOption, $providers = array())
    {
        $this->flagManager = $flagManager;
        $this->cookieManager = $cookieManager;
        $this->useTrustedOption = $useTrustedOption;
        $this->providers = $providers;
    }

    /**
     * Iterate over two factor providers and begin the two factor authentication process
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \Symfony\Component\Security\Core\Authentication\Token\TokenInterface $token
     */
    public function beginAuthentication(Request $request, TokenInterface $token)
    {
        $user = $token->getUser();

        // Skip two factor authentication on trusted computers
        if ($this->useTrustedOption($request, $user) && $this->cookieManager->isTrustedComputer($request, $user)) {
            return;
        }

        // Initialize the two factor process
        foreach ($this->providers as $providerName => $provider) {
            $context = new AuthenticationContext($request, $token, $this->useTrustedOption($request, $user));
            if ($provider->beginAuthentication($context)) {
                $this->flagManager->setBegin($providerName, $token);
            }
        }
    }

    /**
     * Iterate over two factor providers and ask for two factor authentcation.
     * Each provider can return a response. The first response will be returned.
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \Symfony\Component\Security\Core\Authentication\Token\TokenInterface $token
     * @return \Symfony\Component\HttpFoundation\Response|null
     */
    public function requestAuthenticationCode(Request $request, TokenInterface $token)
    {
        $user = $token->getUser();

        // Iterate over two factor providers and ask for completion
        foreach ($this->providers as $providerName => $provider) {
            if ($this->flagManager->isNotAuthenticated($providerName, $token)) {
                $context = new AuthenticationContext($request, $token, $this->useTrustedOption($request, $user));
                $response = $provider->requestAuthenticationCode($context);

                // Set authentication completed
                if ($context->isAuthenticated()) {
                    $this->flagManager->setComplete($providerName, $token);
                }

                // Return response
                if ($response) {

                    // Set trusted cookie
                    if ($context->isAuthenticated() && $request->get("_trusted")) {
                        $cookie = $this->cookieManager->createTrustedCookie($request, $user);
                        $response->headers->setCookie($cookie);
                    }
                    return $response;
                }
            }
        }
        return null;
    }

    /**
     * Return true when trusted computer feature can be used
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param mixed $user
     * @return boolean
     */
    private function useTrustedOption(Request $request, $user)
    {
        return $this->useTrustedOption && $user instanceof TrustedComputerInterface;
    }
}