<?php
namespace Scheb\TwoFactorBundle\Security\TwoFactor;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class AuthenticationContext
{

    /**
     * @var \Symfony\Component\HttpFoundation\Request $request
     */
    private $request;

    /**
     * @var \Symfony\Component\Security\Core\Authentication\Token\TokenInterface $token
     */
    private $token;

    /**
     * If trusted computer feature is enabled
     * @var boolean $useTrustedOption
     */
    private $useTrustedOption;

    /**
     * @var boolean $authenticated
     */
    private $authenticated = false;

    /**
     * Construct a two factor authentication context
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \Symfony\Component\Security\Core\Authentication\Token\TokenInterface $token
     * @param boolean $useTrustedOption
     */
    public function __construct(Request $request, TokenInterface $token, $useTrustedOption)
    {
        $this->request = $request;
        $this->token = $token;
        $this->useTrustedOption = $useTrustedOption;
    }

    /**
     * Return the security token
     *
     * @return \Symfony\Component\Security\Core\Authentication\Token\TokenInterface
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * Return the user object
     *
     * @return \Symfony\Component\Security\Core\Authentication\Token\mixed|null
     */
    public function getUser()
    {
        if (is_object($user = $this->token->getUser())) {
            return $user;
        } else {
            return null;
        }
    }

    /**
     * Return the request
     *
     * @return \Symfony\Component\HttpFoundation\Request
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * Return the session
     *
     * @return \Symfony\Component\HttpFoundation\Session\SessionInterface
     */
    public function getSession()
    {
        return $this->request->getSession();
    }

    /**
     * Return true when trusted computer feature is enabled
     *
     * @return boolean
     */
    public function useTrustedOption()
    {
        return $this->useTrustedOption;
    }

    /**
     * Get authentication status
     *
     * @return boolean
     */
    public function isAuthenticated()
    {
        return $this->authenticated;
    }

    /**
     * Set authentication status
     *
     * @param boolean $authenticated
     */
    public function setAuthenticated($authenticated)
    {
        $this->authenticated = $authenticated;
    }
}
