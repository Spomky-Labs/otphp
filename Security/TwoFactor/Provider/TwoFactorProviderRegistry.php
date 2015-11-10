<?php
namespace Scheb\TwoFactorBundle\Security\TwoFactor\Provider;

use Scheb\TwoFactorBundle\Security\TwoFactor\AuthenticationHandlerInterface;
use Scheb\TwoFactorBundle\Security\TwoFactor\Session\SessionFlagManager;
use Scheb\TwoFactorBundle\Security\TwoFactor\AuthenticationContext;
use Scheb\TwoFactorBundle\Security\TwoFactor\Provider\TwoFactorProviderCollection;
use Symfony\Component\HttpFoundation\Response;

class TwoFactorProviderRegistry implements AuthenticationHandlerInterface
{

    /**
     * Manages session flags
     *
     * @var \Scheb\TwoFactorBundle\Security\TwoFactor\Session\SessionFlagManager $flagManager
     */
    private $flagManager;

    /**
     * List of two-factor providers
     *
     * @var TwoFactorProviderCollection
     */
    private $providerCollection;

    /**
     * Initialize with an array of registered two-factor providers
     *
     * @param \Scheb\TwoFactorBundle\Security\TwoFactor\Session\SessionFlagManager           $flagManager
     * @param \Scheb\TwoFactorBundle\Security\TwoFactor\Provider\TwoFactorProviderCollection $providerCollection
     */
    public function __construct(SessionFlagManager $flagManager, TwoFactorProviderCollection $providerCollection)
    {
        $this->flagManager = $flagManager;
        $this->providerCollection = $providerCollection;
    }

    /**
     * Iterate over two-factor providers and begin the two-factor authentication process
     *
     * @param \Scheb\TwoFactorBundle\Security\TwoFactor\AuthenticationContext $context
     */
    public function beginAuthentication(AuthenticationContext $context)
    {
        foreach ($this->providerCollection->getProviders() as $providerName => $provider) {
            if ($provider->beginAuthentication($context)) {
                $this->flagManager->setBegin($providerName, $context->getToken());
            }
        }
    }

    /**
     * Iterate over two-factor providers and ask for two-factor authentcation.
     * Each provider can return a response. The first response will be returned.
     *
     * @param  \Scheb\TwoFactorBundle\Security\TwoFactor\AuthenticationContext $context
     * @return \Symfony\Component\HttpFoundation\Response|null
     */
    public function requestAuthenticationCode(AuthenticationContext $context)
    {
        $token = $context->getToken();

        // Iterate over two-factor providers and ask for completion
        foreach ($this->providerCollection->getProviders() as $providerName => $provider) {
            if ($this->flagManager->isNotAuthenticated($providerName, $token)) {
                $response = $provider->requestAuthenticationCode($context);

                // Set authentication completed
                if ($context->isAuthenticated()) {
                    $this->flagManager->setComplete($providerName, $token);
                }

                // Return response
                if ($response instanceof Response) {
                    return $response;
                }
            }
        }

        return null;
    }

}
