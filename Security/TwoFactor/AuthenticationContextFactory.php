<?php

namespace Scheb\TwoFactorBundle\Security\TwoFactor;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class AuthenticationContextFactory implements AuthenticationContextFactoryInterface
{
    /**
     * @var string
     */
    private $authenticationContextClass;

    /**
     * @var bool
     */
    private $useTrustedOption;

    public function __construct(string $authenticationContextClass, bool $useTrustedOption)
    {
        $this->authenticationContextClass = $authenticationContextClass;
        $this->useTrustedOption = $useTrustedOption;
    }

    public function create(Request $request, TokenInterface $token, string $firewallName): AuthenticationContextInterface
    {
        return new $this->authenticationContextClass($request, $token, $firewallName, $this->useTrustedOption);
    }
}
