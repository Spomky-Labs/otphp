<?php
namespace Scheb\TwoFactorBundle\Security\TwoFactor\Provider;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;

class TwoFactorProvider
{

    /**
     * Manages session flags
     *
     * @var \Scheb\TwoFactorBundle\Security\TwoFactor\Provider\SessionFlagManager $flagManager
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
     * @param \Scheb\TwoFactorBundle\Security\TwoFactor\Provider\SessionFlagManager $flagManager
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
            if ($provider->beginAuthentication($request, $token)) {
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
            if ($this->flagManager->isIncomplete($providerName, $token)) {
                if ($response = $provider->requestAuthenticationCode($request, $token)) {
                    if ($response instanceof RedirectResponse)
                    {
                        $this->flagManager->setComplete($providerName, $token);
                    }
                    return $response;
                }
            }
        }
        return null;
    }
}