<?php

namespace Scheb\TwoFactorBundle\Security\TwoFactor;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class AuthenticationContext
{
    /**
     * @var Request
     */
    private $request;

    /**
     * @var TokenInterface
     */
    private $token;

    /**
     * If trusted computer feature is enabled.
     *
     * @var bool
     */
    private $useTrustedOption = false;

    /**
     * @var bool
     */
    private $authenticated = false;

    /**
     * Construct a two-factor authentication context.
     *
     * @param Request        $request
     * @param TokenInterface $token
     */
    public function __construct(Request $request, TokenInterface $token)
    {
        $this->request = $request;
        $this->token = $token;
    }

    /**
     * Return the security token.
     *
     * @return TokenInterface
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * Return the user object.
     *
     * @return mixed
     */
    public function getUser()
    {
        if (is_object($user = $this->token->getUser())) {
            return $user;
        }

        return;
    }

    /**
     * Return the request.
     *
     * @return Request
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * Return the session.
     *
     * @return SessionInterface
     */
    public function getSession()
    {
        return $this->request->getSession();
    }

    /**
     * Return true when trusted computer feature is enabled.
     *
     * @return bool
     */
    public function useTrustedOption()
    {
        return $this->useTrustedOption;
    }

    /**
     * Set trusted option flag.
     *
     * @param bool $useTrustedOption
     */
    public function setUseTrustedOption($useTrustedOption)
    {
        $this->useTrustedOption = $useTrustedOption;
    }

    /**
     * Get authentication status.
     *
     * @return bool
     */
    public function isAuthenticated()
    {
        return $this->authenticated;
    }

    /**
     * Set authentication status.
     *
     * @param bool $authenticated
     */
    public function setAuthenticated($authenticated)
    {
        $this->authenticated = $authenticated;
    }
}
