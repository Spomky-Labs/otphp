<?php

namespace Scheb\TwoFactorBundle\Security\TwoFactor;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class AuthenticationContextFactory implements AuthenticationContextFactoryInterface
{
    /**
     * @var string
     */
    protected $authenticationContextClass;

    /**
     * @param string $authenticationContextClass
     */
    public function __construct($authenticationContextClass)
    {
        $this->authenticationContextClass = $authenticationContextClass;
    }

    /**
     * {@inheritDoc}
     */
    public function create(Request $request, TokenInterface $token)
    {
        return new $this->authenticationContextClass($request, $token);
    }
}
