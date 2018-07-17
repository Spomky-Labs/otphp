<?php

/*
 * The MIT License (MIT)
 *
 * Copyright (c) 2014-2018 Spomky-Labs
 *
 * This software may be modified and distributed under the terms
 * of the MIT license.  See the LICENSE file for details.
 */

namespace Scheb\TwoFactorBundle\Security\TwoFactor\Event;

use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class TwoFactorAuthenticationEvent extends Event
{
    /**
     * @var Request
     */
    private $request;

    /**
     * @var TokenInterface
     */
    private $token;

    public function __construct(Request $request, TokenInterface $token)
    {
        $this->request = $request;
        $this->token = $token;
    }

    public function getRequest(): Request
    {
        return $this->request;
    }

    public function getToken(): TokenInterface
    {
        return $this->token;
    }
}
