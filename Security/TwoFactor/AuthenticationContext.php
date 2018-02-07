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
    private $request;

    /**
     * @var TokenInterface
     */
    private $token;

    /**
     * @var string
     */
    private $firewallName;

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

    public function __construct(Request $request, TokenInterface $token, string $firewallName, bool $useTrustedOption)
    {
        $this->request = $request;
        $this->token = $token;
        $this->firewallName = $firewallName;
        $this->useTrustedOption = $useTrustedOption;
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

    public function getFirewallName(): string
    {
        return $this->firewallName;
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
