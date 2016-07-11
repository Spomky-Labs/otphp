<?php

namespace Scheb\TwoFactorBundle\Security\TwoFactor;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

interface AuthenticationContextFactoryInterface
{
    /**
     * @return AuthenticationContextInterface
     */
    public function create(Request $request, TokenInterface $token);
}
