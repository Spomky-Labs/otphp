<?php
namespace Scheb\TwoFactorBundle\Security\TwoFactor\Session;

use Symfony\Component\HttpFoundation\Session\SessionInterface;

class SessionFlagManager
{

    /**
     * @var \Symfony\Component\HttpFoundation\Session\SessionInterface $session
     */
    private $session;

    /**
     * @var \Scheb\TwoFactorBundle\Security\TwoFactor\Session\SessionFlagGenerator $flagGenerator
     */
    private $flagGenerator;

    /**
     * Construct a manager that takes care of session flags
     *
     * @param \Symfony\Component\HttpFoundation\Session\SessionInterface     $session
     * @param \Scheb\TwoFactorBundle\Security\TwoFactor\Session\SessionFlagGenerator $flagGenerator
     */
    public function __construct(SessionInterface $session, SessionFlagGenerator $flagGenerator)
    {
        $this->session = $session;
        $this->flagGenerator = $flagGenerator;
    }

    /**
     * Set session flag to ask for two factor authentication
     *
     * @param string                                                               $provider
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
     * @param string                                                               $provider
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
     * @param  string                                                               $provider
     * @param  \Symfony\Component\Security\Core\Authentication\Token\TokenInterface $token
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
     * @param  string Two-factor provider name
     * @param  \Symfony\Component\Security\Core\Authentication\Token\TokenInterface $token
     * @return string
     */
    protected function getSessionFlag($provider, $token)
    {
        return $this->flagGenerator->getSessionFlag($provider, $token);
    }

}
