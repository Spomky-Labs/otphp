<?php

/*
 * The MIT License (MIT)
 *
 * Copyright (c) 2014-2018 Spomky-Labs
 *
 * This software may be modified and distributed under the terms
 * of the MIT license.  See the LICENSE file for details.
 */

namespace Scheb\TwoFactorBundle\Security\Authentication\Exception;

use Symfony\Component\Security\Core\Exception\AuthenticationException;

class InvalidTwoFactorCodeException extends AuthenticationException
{
    public function getMessageKey()
    {
        return 'Invalid two-factor authentication code.';
    }
}
