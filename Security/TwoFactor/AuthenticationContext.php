<?php

namespace Scheb\TwoFactorBundle\Security\TwoFactor;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class AuthenticationContext implements AuthenticationContextInterface
{
    /**
     * @var Request
     */
    protected $request;

    /**
     * @var TokenInterface
     */
    protected $token;

    /**
     * If trusted computer feature is enabled.
     *
     * @var bool
     */
    protected $useTrustedOption = false;

    /**
     * @var bool
     */
    protected $authenticated = false;

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
     * {@inheritDoc}
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * {@inheritDoc}
     */
    public function getUser()
    {
        if (is_object($user = $this->token->getUser())) {
            return $user;
        }

        return null;
    }

    /**
     * {@inheritDoc}
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * {@inheritDoc}
     */
    public function getSession()
    {
        return $this->request->getSession();
    }

    /**
     * {@inheritDoc}
     */
    public function useTrustedOption()
    {
        return $this->useTrustedOption;
    }

    /**
     * {@inheritDoc}
     */
    public function setUseTrustedOption($useTrustedOption)
    {
        $this->useTrustedOption = $useTrustedOption;
    }

    /**
     * {@inheritDoc}
     */
    public function isAuthenticated()
    {
        return $this->authenticated;
    }

    /**
     * {@inheritDoc}
     */
    public function setAuthenticated($authenticated)
    {
        $this->authenticated = $authenticated;
    }
}
