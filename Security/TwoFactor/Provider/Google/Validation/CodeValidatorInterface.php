<?php

namespace Scheb\TwoFactorBundle\Security\TwoFactor\Provider\Google\Validation;

use Scheb\TwoFactorBundle\Model\Google\TwoFactorInterface;

interface CodeValidatorInterface
{
    /**
     * Validates the code, which was entered by the user.
     *
     * @param TwoFactorInterface $user
     * @param int                $code
     *
     * @return bool
     */
    public function checkCode(TwoFactorInterface $user, $code);
}
