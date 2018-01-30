<?php

namespace Scheb\TwoFactorBundle\Security\TwoFactor\Provider\Email\Validation;

use Scheb\TwoFactorBundle\Model\Email\TwoFactorInterface;

interface CodeValidatorInterface
{
    /**
     * Validates the code, which was entered by the user.
     *
     * @param TwoFactorInterface $user
     * @param string             $code
     *
     * @return bool
     */
    public function checkCode(TwoFactorInterface $user, string $code);
}
