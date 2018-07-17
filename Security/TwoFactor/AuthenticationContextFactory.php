<?php

/*
 * The MIT License (MIT)
 *
 * Copyright (c) 2014-2018 Spomky-Labs
 *
 * This software may be modified and distributed under the terms
 * of the MIT license.  See the LICENSE file for details.
 */

namespace Scheb\TwoFactorBundle\Security\TwoFactor;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class AuthenticationContextFactory implements AuthenticationContextFactoryInterface
{
    /**
     * @var string
     */
    private $authenticationContextClass;

    public function __construct(string $authenticationContextClass)
    {
        $this->authenticationContextClass = $authenticationContextClass;
    }

    public function create(Request $request, TokenInterface $token, string $firewallName): AuthenticationContextInterface
    {
        return new $this->authenticationContextClass($request, $token, $firewallName);
    }
}
