<?php

namespace Scheb\TwoFactorBundle\Security\TwoFactor;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
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

    public function __construct(Request $request, TokenInterface $token)
    {
        $this->request = $request;
        $this->token = $token;
    }

    public function getToken(): TokenInterface
    {
        return $this->token;
    }

    public function getUser()
    {
        if (is_object($user = $this->token->getUser())) {
            return $user;
        }

        return null;
    }

    public function getRequest(): Request
    {
        return $this->request;
    }

    public function getSession(): SessionInterface
    {
        return $this->request->getSession();
    }

    public function useTrustedOption(): bool
    {
        return $this->useTrustedOption;
    }

    public function setUseTrustedOption(bool $useTrustedOption): void
    {
        $this->useTrustedOption = $useTrustedOption;
    }

    public function isAuthenticated(): bool
    {
        return $this->authenticated;
    }

    public function setAuthenticated(bool $authenticated): void
    {
        $this->authenticated = $authenticated;
    }
}
