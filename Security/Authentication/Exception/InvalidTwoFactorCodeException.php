<?php

namespace Scheb\TwoFactorBundle\Security\Authentication\Exception;

use Symfony\Component\Security\Core\Exception\AuthenticationException;

class InvalidTwoFactorCodeException extends AuthenticationException
{
    public function getMessageKey()
    {
        return 'Invalid two-factor authentication code.';
    }
}
