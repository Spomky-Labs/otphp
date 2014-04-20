<?php
namespace Scheb\TwoFactorBundle\Security\TwoFactor\Provider;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Scheb\TwoFactorBundle\Security\TwoFactor\SessionFlagManager;
use Scheb\TwoFactorBundle\Security\TwoFactor\AuthenticationContext;

class TwoFactorProvider
{

    /**
     * Manages session flags
     *
     * @var \Scheb\TwoFactorBundle\Security\TwoFactor\SessionFlagManager $flagManager
     */
    private $flagManager;

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
     * @param array $providers
     */
    public function __construct(SessionFlagManager $flagManager, $providers = array())
    {
        $this->flagManager = $flagManager;
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
        foreach ($this->providers as $providerName => $provider) {
            $context = new AuthenticationContext($request, $token);
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
        foreach ($this->providers as $providerName => $provider) {
            if ($this->flagManager->isNotAuthenticated($providerName, $token)) {
                $context = new AuthenticationContext($request, $token);
                $response = $provider->requestAuthenticationCode($context);
                if ($context->isAuthenticated())
                {
                    $this->flagManager->setComplete($providerName, $token);
                }
                if ($response) {
                    return $response;
                }
            }
        }
        return null;
    }
}