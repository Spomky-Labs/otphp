<?php
namespace Scheb\TwoFactorBundle\Security\TwoFactor;

use Symfony\Component\HttpFoundation\Session\SessionInterface;

class SessionFlagManager
{

    /**
     * @var \Symfony\Component\HttpFoundation\Session\SessionInterface $session
     */
    private $session;

    /**
     * Construct a manager that takes care of session flags
     *
     * @param \Symfony\Component\HttpFoundation\Session\SessionInterface $session
     */
    public function __construct(SessionInterface $session)
    {
        $this->session = $session;
    }

    /**
     * Set session flag to ask for two factor authentication
     *
     * @param string $provider
     * @param \Symfony\Component\Security\Core\Authentication\Token\TokenInterface $token
     */
    public function setBegin($provider, $token)
    {
        $sessionFlag = $this->getSessionFlag($provider, $token);
        $this->session->set($sessionFlag, false);
    }

    /**
     * Set session flag completed
     *
     * @param string $provider
     * @param \Symfony\Component\Security\Core\Authentication\Token\TokenInterface $token
     */
    public function setComplete($provider, $token)
    {
        $sessionFlag = $this->getSessionFlag($provider, $token);
        return $this->session->set($sessionFlag, true);
    }

    /**
     * Check if session flag is set and is not complete
     *
     * @param string $provider
     * @param \Symfony\Component\Security\Core\Authentication\Token\TokenInterface $token
     * @return boolean
     */
    public function isNotAuthenticated($provider, $token)
    {
        $sessionFlag = $this->getSessionFlag($provider, $token);
        return $this->session->has($sessionFlag) && ! $this->session->get($sessionFlag);
    }

    /**
     * Generate session token
     *
     * @param \Symfony\Component\Security\Core\Authentication\Token\TokenInterface $token
     * @return string
     */
    protected function getSessionFlag($provider, $token)
    {
        // Support provider key
        $providerKey = "any";
        if (method_exists($token, "getProviderKey")) {
            $providerKey = $token->getProviderKey();
        }
        return sprintf('two_factor_%s_%s_%s', $provider, $providerKey, $token->getUsername());
    }
}