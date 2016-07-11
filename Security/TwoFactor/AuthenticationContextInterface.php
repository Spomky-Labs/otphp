<?php

namespace Scheb\TwoFactorBundle\Security\TwoFactor;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

interface AuthenticationContextInterface
{
    /**
     * Return the security token.
     *
     * @return TokenInterface
     */
    public function getToken();

    /**
     * Return the user object.
     *
     * @return mixed
     */
    public function getUser();

    /**
     * Return the request.
     *
     * @return Request
     */
    public function getRequest();

    /**
     * Return the session.
     *
     * @return SessionInterface
     */
    public function getSession();

    /**
     * Return true when trusted computer feature is enabled.
     *
     * @return bool
     */
    public function useTrustedOption();

    /**
     * Set trusted option flag.
     *
     * @param bool $useTrustedOption
     */
    public function setUseTrustedOption($useTrustedOption);

    /**
     * Get authentication status.
     *
     * @return bool
     */
    public function isAuthenticated();

    /**
     * Set authentication status.
     *
     * @param bool $authenticated
     */
    public function setAuthenticated($authenticated);
}
