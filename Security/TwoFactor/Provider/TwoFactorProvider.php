<?php
namespace Scheb\TwoFactorBundle\Security\TwoFactor\Provider;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class TwoFactorProvider implements TwoFactorProviderInterface
{

    /**
     * List of two factor providers
     *
     * @var array $providers
     */
    private $providers;

    /**
     * Initialize with an array of registered two factor providers
     *
     * @param array $providers
     */
    public function __construct($providers = array())
    {
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
        foreach ($this->providers as $provider) {
            $provider->beginAuthentication($request, $token);
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
        foreach ($this->providers as $provider) {
            if ($response = $provider->requestAuthenticationCode($request, $token)) {
                return $response;
            }
        }
        return null;
    }
}